<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" />

    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h4 class="modal-title" style="font-weight: bold">Order Detail</h4>
          </div>
          <div class="modal-body">
            <table class="table">
              <tbody>
                <tr>
                  <td width="80px" style="text-align: right">Table No:</td>
                  <td style="text-align: left">{{ order.table_name }}</td>
                  <td width="80px" style="text-align: right">Invoice #:</td>
                  <td style="text-align: left">{{ order.invoice_no }}</td>
                </tr>
                <tr>
                  <td style="width: 60px; text-align: right">Cashier:</td>
                  <td style="text-align: left; width: 100px" class="text-capitalize">{{
                    order.operator?.username }}</td>
                  <td style="width: 60px; text-align: right">Date:</td>
                  <td style="text-align: left; width: 100px">{{ dateFormat(order.created_at) }}</td>
                </tr>
              </tbody>
            </table>
            <table class="table">
              <thead>
                <tr class="table-dark">
                  <th>No</th>
                  <th>Descripiton</th>
                  <th class="text-center">QTY</th>
                  <th class="text-end">Unit Price ($)</th>
                  <th class="text-end">Discount (%)</th>
                  <th class="text-end">Total ($)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(data, index) in order.order_details">
                  <td>{{ index + 1 }}</td>
                  <td>{{ data.description }}</td>
                  <td class="text-center">{{ data.qty }}</td>
                  <td class="text-end">{{ (data.unit_price) }} </td>
                  <td class="text-end">{{ data.discount }}</td>
                  <td class="text-end">
                    {{ (data.unit_price * data.qty * (1 - data.discount / 100)) }} </td>
                </tr>
              </tbody>
            </table>
            <hr />
            <table class="table">
              <tbody>
                <tr v-if="order && order.discount > 0">
                  <td style="text-align: right">Grand Total ($) :</td>
                  <td style="text-align: right; width: 100px;">{{ numberFormat(order?.total) }}</td>
                </tr>
                <tr v-if="order && order.discount > 0">
                  <td style="text-align: right">
                    Discount ({{ order.discount }}%) :
                  </td>
                  <td style="text-align: right;">
                    {{ numberFormat((order.total * order.discount) / 100) }}
                  </td>
                </tr>
                <tr>
                  <th style="text-align: right">Total ($) :</th>
                  <th style="text-align: right; width: 100px;">{{ numberFormat(order?.net_amount) }}</th>
                </tr>
                <tr v-if="order?.receive_amount > 0">
                  <td style="text-align: right">Receive Amount($) :</td>
                  <td style="text-align: right">{{ numberFormat(order?.receive_amount, 2) }}</td>
                </tr>
                <tr v-if="order?.receive_amount - order?.net_amount">
                  <td style="text-align: right">Change ($) :</td>
                  <td style="text-align: right">{{
                    numberFormat(order?.receive_amount - order?.net_amount, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <button type="button" class="btn btn-success" style="float: right" @click="exportToExcel">
      <i class="bi bi-file-earmark-excel"></i> Export to Excel
    </button>
    <div class="pagetitle">
      <h1>Sale History</h1>
    </div>
    <section class="section">
      <div class="col">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="searchData">
              <div class="row pt-4">
                <div class="col-md-10">
                  <div class="row justify-content-start">
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Invoice No</label>
                      <input type="text" class="form-control" v-model="filter.invoice_no" placeholder="Search..." />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">From Date</label>
                      <flat-pickr v-model="filter.from_date" class="form-control" :config="dateFilterConfig"
                        @change="onStartChange" />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">To Date</label>
                      <flat-pickr v-model="filter.to_date" class="form-control" :config="dateFilterConfig"
                        @change="onEndChange" />
                    </div>
                  </div>
                </div>
                <div class="col-md-2 align-self-end">
                  <button type="submit" class="btn btn-secondary pt-1" style="float: right">
                    <i class="bi bi-search"></i> Search
                  </button>
                </div>
              </div>
            </form>
            <hr class="text-secondary" />
            <table class="table">
              <thead>
                <tr class="table-dark">
                  <th class="text-center">
                    Total Amount
                  </th>
                  <th class="text-center">
                    Total Discount
                  </th>
                  <th class="text-center">
                    Net Amount
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th class="text-center text-primary">
                    {{ currencyFormat(sale_summary.grand_total ?? 0) }}
                  </th>
                  <th class="text-center text-danger">
                    {{ currencyFormat(sale_summary.total_discount ?? 0) }}
                  </th>
                  <th class="text-center text-success">
                    {{ currencyFormat(sale_summary.net_amount ?? 0) }}
                  </th>
                </tr>
              </tbody>
            </table>

            <table class="table table-striped">
              <thead>
                <tr class="table-dark">
                  <th style="width: 50px">#</th>
                  <th @click="sortData('orders.invoice_no')" style="cursor: pointer">
                    Invoice No <i class="text-secondary"
                      :class="filter.sortBy == 'orders.invoice_no' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th @click="sortData('tables.name')" style="cursor: pointer">
                    Table No <i class="text-secondary"
                      :class="filter.sortBy == 'orders.name' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th @click="sortData('orders.grand_total')" style="cursor: pointer" class="text-end">
                    Total Amount<i class="text-secondary"
                      :class="filter.sortBy == 'orders.grand_total' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th @click="sortData('orders.total_discount')" style="cursor: pointer" class="text-end">
                    Discount<i class="text-secondary"
                      :class="filter.sortBy == 'orders.total_discount' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th @click="sortData('orders.net_amount')" style="cursor: pointer" class="text-end">
                    Net Amount<i class="text-secondary"
                      :class="filter.sortBy == 'orders.net_amount' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th @click="sortData('orders.created_at')" style="cursor: pointer;">
                    Date <i class="text-secondary"
                      :class="filter.sortBy == 'orders.created_at' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th>
                    Cashier
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="dataList && dataList.data && dataList.data.length > 0" v-for="(d, index) in dataList.data"
                  :key="d.id">
                  <th scope="row">{{ dataList.from + index }}</th>
                  <td><button class="btn btn-link p-0" @click="viewDetail(d.id)">{{ d.invoice_no }}</button></td>
                  <td>{{ d.table_name }}</td>
                  <td class="text-end">{{ currencyFormat(d.grand_total) }}</td>
                  <td class="text-end">{{ currencyFormat(d.total_discount) }}</td>
                  <td class="text-end">{{ currencyFormat(d.net_amount) }}</td>
                  <td>{{ dateFormat(d.created_at) }}</td>
                  <td class="text-capitalize">{{ d.cashier }}</td>
                </tr>
                <tr v-else>
                  <td colspan="10" class="shadow-none">
                    No record found
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- Pagination -->
            <div class="d-flex justify-content-end">
              <nav v-if="dataList.links && dataList.links.length > 3">
                <ul class="pagination">
                  <li :class="['page-item', data.url ? '' : 'disabled', data.active ? 'active' : '']"
                    v-for="data in dataList.links">
                    <span class="page-link" style="cursor: pointer" v-html="data.label" v-if="data.url && !data.active"
                      @click="paginate(data.url.substring(data.url.lastIndexOf('?page=') + 6))"></span>
                    <span class="page-link" v-html="data.label" v-else></span>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
<script setup>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import { onMounted, onUnmounted, ref } from 'vue';
import { currencyFormat, dateFilterConfig, dateFormat, numberFormat } from '../../helper.js';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { Modal } from 'bootstrap';

const isLoading = ref(false);
const formModalInstance = ref(null);
const formModal = ref(null);
const order = ref({});
const dataList = ref([]);
const sale_summary = ref({});
const filter = ref(
  {
    invoice_no: null,
    from_date: new Date(),
    to_date: new Date(),
    sortBy: 'created_at',
    orderBy: 'desc',
    page: 1
  }
);

onMounted(() => {
  if (formModal.value) {
    formModalInstance.value = new Modal(formModal.value);
    formModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
  }
  getSummaryData();
  getData(true);
});
onUnmounted(() => {
  if (formModalInstance.value) {
    formModalInstance.value.dispose();
  }
});

const viewDetail = (id) => {
  isLoading.value = true;
  axios.get("api/report/show-order-detail/" + id).then((response) => {
    if (response.data.success) {
      order.value = response.data.data;
      formModalInstance.value.show();
    }
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const onStartChange = (selectedDates, dateStr, instance) => {
  dateFilterConfig.value.minDate = dateStr;
};

const onEndChange = (selectedDates, dateStr, instance) => {
  dateFilterConfig.value.maxDate = dateStr;
};

// sort
const sortData = (field) => {
  if (filter.value.sortBy === field) {
    filter.value.orderBy = filter.value.orderBy == 'asc' ? 'desc' : 'asc';
  } else {
    filter.value.sortBy = field;
    filter.value.orderBy = 'asc';
  }
  getData();
};

// Pagination
const paginate = (page_number) => {
  filter.value.page = page_number;
  if (page_number > dataList.last_page) {
    filter.value.page = dataList.last_page;
  }
  if (page_number <= 0) {
    filter.value.page = 1;
  }
  getData();
};

// search data
const searchData = () => {
  getSummaryData();
  getData(true);
};

// load data
const getData = (resetPge = false) => {
  isLoading.value = true;
  if (resetPge)
    filter.value.page = 1;
  axios.post("api/report/sale-history", filter.value).then((response) => {
    if (response.data.success) {
      dataList.value = response.data.data;
    }
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// get summary data
const getSummaryData = () => {
  axios.post("api/report/sale-history-summary", filter.value).then((response) => {
    if (response.data.success) {
      console.log(response.data.data);
      sale_summary.value = response.data.data;
    }
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// export to excel
const exportToExcel = () => {
  isLoading.value = true;
  axios.post("api/report/export-sale-history", filter.value, {
    responseType: 'blob' // REQUIRED!
  }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'Sale History Report.xlsx');
    document.body.appendChild(link);
    link.click();
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};
</script>