const chartPalette = ['#2563eb', '#10b981', '#f59e0b', '#ef4444'];
let ApexChartsConstructor;

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

function baseOptions(element) {
    const theme = chartTheme();
    const labels = parseChartData(element, 'chartLabels', []);

    return {
        chart: {
            background: 'transparent',
            fontFamily: 'inherit',
            height: 280,
            parentHeightOffset: 0,
            toolbar: { show: false },
            zoom: { enabled: false },
        },
        colors: chartPalette,
        dataLabels: { enabled: false },
        grid: {
            borderColor: theme.gridColor,
            strokeDashArray: 4,
        },
        labels,
        legend: {
            labels: { colors: theme.foreColor },
        },
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        theme: { mode: theme.mode },
        tooltip: { theme: theme.tooltipTheme },
        xaxis: {
            categories: labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                rotate: -45,
                style: { colors: theme.foreColor },
            },
        },
        yaxis: {
            labels: {
                formatter: (value) => Math.round(value),
                style: { colors: theme.foreColor },
            },
        },
    };
}

function optionsFor(element) {
    const type = element.dataset.chartType;
    const series = parseChartData(element, 'chartSeries', []);
    const options = baseOptions(element);

    if (type === 'donut') {
        return {
            ...options,
            chart: {
                ...options.chart,
                height: 260,
                type: 'donut',
            },
            labels: parseChartData(element, 'chartLabels', []),
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                    },
                },
            },
            series,
            stroke: {
                colors: [isDarkMode() ? '#18181b' : '#ffffff'],
                width: 2,
            },
        };
    }

    if (type === 'bar') {
        return {
            ...options,
            chart: {
                ...options.chart,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    columnWidth: '52%',
                },
            },
            series,
            stroke: {
                show: false,
            },
        };
    }

    return {
        ...options,
        chart: {
            ...options.chart,
            type: 'area',
        },
        fill: {
            gradient: {
                opacityFrom: 0.32,
                opacityTo: 0,
                shadeIntensity: 0.4,
                type: 'vertical',
            },
            type: 'gradient',
        },
        series,
    };
}

async function getApexCharts() {
    if (! ApexChartsConstructor) {
        ApexChartsConstructor = (await import('apexcharts')).default;
    }

    return ApexChartsConstructor;
}

async function renderDashboardCharts() {
    const elements = document.querySelectorAll('.js-dashboard-chart');

    if (elements.length === 0) {
        return;
    }

    const ApexCharts = await getApexCharts();

    elements.forEach((element) => {
        if (element._dashboardChart) {
            element._dashboardChart.destroy();
        }

        element._dashboardChart = new ApexCharts(element, optionsFor(element));
        element._dashboardChart.render();
    });
}

document.addEventListener('DOMContentLoaded', renderDashboardCharts);
document.addEventListener('livewire:navigated', renderDashboardCharts);

new MutationObserver(renderDashboardCharts).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
});
