import Chart from 'chart.js/auto';

const chartPalette = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function parseChartData(element, key, fallback) {
    try {
        return JSON.parse(element.dataset[key] || JSON.stringify(fallback));
    } catch {
        return fallback;
    }
}

function chartTheme() {
    const dark = isDarkMode();

    return {
        mode: dark ? 'dark' : 'light',
        foreColor: dark ? '#d4d4d8' : '#52525b',
        gridColor: dark ? '#27272a' : '#e4e4e7',
        tooltipTheme: dark ? 'dark' : 'light',
    };
}

function normalizeDatasets(series, type) {
    if (! Array.isArray(series)) {
        return [];
    }

    if (series.length > 0 && typeof series[0] === 'object' && 'data' in series[0]) {
        return series.map((item, index) => ({
            label: item.label || item.name || `Series ${index + 1}`,
            data: item.data || [],
            borderColor: item.borderColor || chartPalette[index % chartPalette.length],
            backgroundColor: item.backgroundColor || (type === 'doughnut'
                ? chartPalette
                : chartPalette[index % chartPalette.length]),
            fill: item.fill ?? (type === 'line'),
            tension: item.tension ?? 0.35,
        }));
    }

    return [{
        label: 'Value',
        data: series,
        backgroundColor: chartPalette,
        borderColor: chartPalette,
    }];
}

function chartType(type) {
    if (type === 'area') {
        return 'line';
    }

    if (type === 'donut') {
        return 'doughnut';
    }

    return type || 'line';
}

function baseOptions(element) {
    const theme = chartTheme();
    const type = chartType(element.dataset.chartType);

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: type === 'doughnut' ? 'bottom' : 'top',
                labels: {
                    color: theme.foreColor,
                    boxWidth: 10,
                    boxHeight: 10,
                    usePointStyle: true,
                },
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            },
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
        scales: type === 'doughnut' ? {} : {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: theme.foreColor,
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 8,
                },
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: theme.gridColor,
                },
                ticks: {
                    color: theme.foreColor,
                    precision: 0,
                },
            },
        },
    };
}

function configFor(element) {
    const type = chartType(element.dataset.chartType);
    const labels = parseChartData(element, 'chartLabels', []);
    const series = parseChartData(element, 'chartSeries', []);

    return {
        type,
        data: {
            labels,
            datasets: normalizeDatasets(series, type),
        },
        options: baseOptions(element),
    };
}

function renderDashboardCharts() {
    const elements = document.querySelectorAll('.js-dashboard-chart');

    if (elements.length === 0) {
        return;
    }

    elements.forEach((element) => {
        if (element._dashboardChart) {
            element._dashboardChart.destroy();
        }

        const canvas = element.matches('canvas')
            ? element
            : element.querySelector('canvas');

        if (! canvas) {
            return;
        }

        element._dashboardChart = new Chart(canvas, configFor(element));
    });
}

document.addEventListener('DOMContentLoaded', renderDashboardCharts);
document.addEventListener('livewire:navigated', renderDashboardCharts);

new MutationObserver(renderDashboardCharts).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
});

function decodeBase64(value) {
    try {
        return decodeURIComponent(escape(atob(value || '')));
    } catch {
        return '';
    }
}

function encodeHtml(value) {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function syncEditor(root, html) {
    const model = root.dataset.model;
    const componentRoot = root.closest('[wire\\:id]');
    const component = componentRoot && window.Livewire
        ? window.Livewire.find(componentRoot.getAttribute('wire:id'))
        : null;

    root.dataset.currentHtml = html;
    root.querySelector('[data-editor-source]').value = html;
    root.querySelector('[data-editor-preview]').innerHTML = html;

    if (component?.$wire?.set && model) {
        component.$wire.set(model, html);
    } else if (component?.set && model) {
        component.set(model, html);
    }
}

function focusEditor(root) {
    const editor = root.querySelector('[data-editor-surface]');
    editor.focus();
}

function execEditorCommand(root, command, value = null) {
    focusEditor(root);
    document.execCommand(command, false, value);
    syncEditor(root, root.querySelector('[data-editor-surface]').innerHTML);
}

function insertEditorHtml(root, html) {
    focusEditor(root);
    document.execCommand('insertHTML', false, html);
    syncEditor(root, root.querySelector('[data-editor-surface]').innerHTML);
}

function setEditorMode(root, mode) {
    root.dataset.mode = mode;

    const surface = root.querySelector('[data-editor-surface]');
    const source = root.querySelector('[data-editor-source]');
    const preview = root.querySelector('[data-editor-preview]');

    surface.classList.toggle('hidden', mode !== 'design');
    source.classList.toggle('hidden', mode !== 'source');
    preview.classList.toggle('hidden', mode !== 'preview');

    if (mode === 'source') {
        source.value = root.dataset.currentHtml || surface.innerHTML;
        source.focus();
    }

    if (mode === 'preview') {
        preview.innerHTML = root.dataset.currentHtml || source.value || surface.innerHTML;
    }
}

function initializeTemplateEditors() {
    document.querySelectorAll('[data-template-editor]').forEach((root) => {
        if (root.dataset.initialized === 'true') {
            return;
        }

        root.dataset.initialized = 'true';

        const surface = root.querySelector('[data-editor-surface]');
        const source = root.querySelector('[data-editor-source]');
        const initialHtml = decodeBase64(root.dataset.initialHtml);

        surface.innerHTML = initialHtml;
        syncEditor(root, initialHtml);

        root.querySelectorAll('[data-editor-command]').forEach((button) => {
            button.addEventListener('click', () => {
                const command = button.dataset.editorCommand;

                if (command === 'createLink') {
                    const url = window.prompt('Enter link URL');

                    if (url) {
                        execEditorCommand(root, command, url);
                    }

                    return;
                }

                execEditorCommand(root, command);
            });
        });

        root.querySelectorAll('[data-editor-block]').forEach((button) => {
            button.addEventListener('click', () => {
                execEditorCommand(root, 'formatBlock', button.dataset.editorBlock);
            });
        });

        root.querySelectorAll('[data-editor-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                const nextMode = button.dataset.editorMode;

                if (root.dataset.mode === 'source' && nextMode !== 'source') {
                    surface.innerHTML = source.value;
                    syncEditor(root, source.value);
                }

                setEditorMode(root, nextMode);
            });
        });

        root.querySelectorAll('[data-editor-variable]').forEach((button) => {
            button.addEventListener('click', () => {
                insertEditorHtml(root, encodeHtml(button.dataset.editorVariable));
            });
        });

        root.querySelector('[data-editor-image]').addEventListener('change', (event) => {
            const file = event.target.files?.[0];

            if (! file || ! file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', () => {
                insertEditorHtml(root, `<img src="${reader.result}" alt="" style="max-width: 100%; height: auto;">`);
                event.target.value = '';
            });

            reader.readAsDataURL(file);
        });

        surface.addEventListener('input', () => {
            syncEditor(root, surface.innerHTML);
        });

        source.addEventListener('input', () => {
            surface.innerHTML = source.value;
            syncEditor(root, source.value);
        });

        setEditorMode(root, 'design');
    });
}

document.addEventListener('DOMContentLoaded', initializeTemplateEditors);
document.addEventListener('livewire:navigated', initializeTemplateEditors);
document.addEventListener('livewire:initialized', initializeTemplateEditors);
