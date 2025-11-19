<template>
    <div class="vl-parent">
        <loading v-model:active="isLoading" :is-full-page="true" />
        <div class="pagetitle">
            <h1>Dashboard</h1>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="me-2">
                                <div class="display-6 text-white">
                                    {{ currencyFormat(summaryData.grand_total) }}
                                </div>
                                <div class="card-text fs-6 mt-2">Daily Total Sale</div>
                            </div>
                            <div style="color: lightblue"><i class="bi bi-cash-stack display-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow bg-danger text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="me-2">
                                <div class="display-6 text-white">{{ currencyFormat(summaryData.total_discount) }}</div>
                                <div class="card-text fs-6 mt-2">Daily Total Disount</div>
                            </div>
                            <div style="color: lightgray;"><i class="bi bi-percent display-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow bg-success text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="me-2">
                                <div class="display-6 text-white">{{ currencyFormat(summaryData.net_amount) }}</div>
                                <div class="card-text fs-6 mt-2">Daily Net Amount</div>
                            </div>
                            <div style="color: lightblue"><i class="bi bi-coin display-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <strong class="card-header bg-dark text-white py-2">
                        <i class="fas fa-chart-area me-1"></i>
                        Daily Sale Report By Categories
                    </strong>
                    <div class="card-body">
                        <VueApexCharts height="390px" :options="ProductCategoryOptions" :series="ProductCategorySeries">
                        </VueApexCharts>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table table-hover shadow align-middle bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Product Name</th>
                            <th class="text-end">QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(data, index) in Top10Products">
                            <td>{{ index + 1 }}</td>
                            <td>{{ data.description }}</td>
                            <td class="text-end">
                                {{ data.qty }}
                            </td>
                        </tr>
                        <tr v-for="item in (10 - Top10Products.length)" :key="item">
                            <td>{{ (Top10Products.length) + item }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col">
            <div class="card shadow mb-3">
                <strong class="card-header bg-dark text-white py-2">
                    <i class="fas fa-chart-bar me-1"></i>
                    15 Days Total Sale Amount
                </strong>
                <div class="card-body">
                    <VueApexCharts height="400px" type="bar" :options="chartOptions" :series="series">
                    </VueApexCharts>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineAsyncComponent } from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import { currencyFormat } from "../../helper";
import { onMounted, ref } from "vue";
const VueApexCharts = defineAsyncComponent(() =>
    import('vue3-apexcharts')
);
const isLoading = ref(false);
const summaryData = ref({
    grand_total: 0,
    total_discount: 0,
    net_amount: 0
});
const Top10Products = ref([]);

const ProductCategorySeries = ref([]);
const ProductCategoryOptions = ref({
    colors: ['#3366cc', '#660066', '#006600', '#cc0066', '#996633', '#006666', '#993399', '#999966', '#ffcc99', '#33cc33', '#cccc00'],
    fill: {
        colors: ['#3366cc', '#660066', '#006600', '#cc0066', '#996633', '#006666', '#993399', '#999966', '#ffcc99', '#33cc33', '#cccc00']
    },
    chart: {
        type: 'pie',
    },
    labels: [],
    responsive: [{
        options: {
            legend: {
                position: 'bottom'
            }
        }
    }],
    legend: {
        position: 'bottom'
    },
    theme: {
        palette: 'palette2' // upto palette10
    },

    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return val.toFixed(2) + "%"
        },
        dropShadow: {
        }
    },
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (value) {
                try {
                    if (typeof value !== "number") {
                        if (value && !isNaN(value)) {
                            value = parseFloat(value);
                        } else {
                            return value;
                        }
                    }
                    var formatter = new Intl.NumberFormat("en-US", {
                        style: "decimal",
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
                    return "$" + formatter.format(value);
                } catch (ex) {
                    return "$" + value;
                }
            }
        }
    },
});

const series = ref([
    {
        name: 'Net Amount',
        data: []
    },
]);
const chartOptions = ref({
    chart: {
        //height: 350,
        type: 'bar',
        stacked: true,
        toolbar: {
            show: false,
        },
    },
    stroke: {
        curve: 'smooth',
    },

    colors: ['#198754', '#0d6efd', '#9C27B0'],
    fill: {
        opacity: 1,
        gradient: {
            inverseColors: false,
            shade: 'light',
            type: "vertical",
            opacityFrom: 0.85,
            opacityTo: 0.55,
        },
        colors: ['#198754', '#0d6efd', '#9C27B0'],
    },
    labels: [],
    markers: {
        size: 0
    },

    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (value) {
                try {
                    if (typeof value !== "number") {
                        if (value && !isNaN(value)) {
                            value = parseFloat(value);
                        } else {
                            return value;
                        }
                    }
                    var formatter = new Intl.NumberFormat("en-US", {
                        style: "decimal",
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
                    return "$" + formatter.format(value);
                } catch (ex) {
                    return "$" + value;
                }
            }
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function (value) {
            try {
                if (typeof value !== "number") {
                    if (value && !isNaN(value)) {
                        value = parseFloat(value);
                    } else {
                        return value;
                    }
                }
                var formatter = new Intl.NumberFormat("en-US", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                return "$" + formatter.format(value);
            } catch (ex) {
                return "$" + value;
            }
        }
    }
});

onMounted(() => {
    isLoading.value = true;
    axios.get("api/dashboard")
        .then((response) => {
            if (response.data.success) {
                // Daily Summary Data
                if (response.data.sale_categories && response.data.sale_categories.length > 0) {
                    var d = response.data.sale_categories;
                    for (var i = 0; i < d.length; i++) {
                        ProductCategoryOptions.value.labels.push(d[i].name);
                        ProductCategorySeries.value.push(Number(d[i].total) - Number(d[i].discount));

                        summaryData.value.grand_total += Number(d[i].total);
                        summaryData.value.total_discount += Number(d[i].discount);
                    };
                    summaryData.value.net_amount = (summaryData.value.grand_total - summaryData.value.total_discount) ?? 0;
                }

                // Top 10 Products
                Top10Products.value = response.data.top_products;

                // Last 15 days
                if (response.data.bar_data && response.data.bar_data.length > 0) {

                    response.data.bar_data.forEach(element => {
                        chartOptions.value.labels.push(element.date);
                        series.value[0].data.push(Number(element.total));
                    });
                }
            }
        })
        .catch((ex) => {
            console.log(ex);
        })
        .finally(() => {
            isLoading.value = false;
        });
});
</script>