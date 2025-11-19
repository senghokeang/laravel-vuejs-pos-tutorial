<template>
  <div class="vl-parent">
    <loading v-model:active="isLoading" :is-full-page="true" />
    <ShareModal ref="messageBox"></ShareModal>

    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
      data-bs-backdrop="static" data-bs-focus="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h5 class="modal-title" style="font-weight: bold">
              Change Password
            </h5>
          </div>
          <div class="modal-body">
            <form @submit.prevent="changePassword" id="formPassword">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label">Name</label>
                  <input type="text" class="form-control" :value="auth.user?.username" disabled />
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Old Password</label>
                  <input type="password" :disabled="isLoading"
                    :class="['form-control', { 'is-invalid': errors.old_password }]" v-model="form.old_password"
                    ref="autofocus" />
                  <span v-if="errors.old_password" class="invalid-feedback"> {{
                    errors.old_password[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">New Password</label>
                  <input type="password" :disabled="isLoading"
                    :class="['form-control', { 'is-invalid': errors.new_password }]" v-model="form.new_password" />
                  <span v-if="errors.new_password" class="invalid-feedback"> {{
                    errors.new_password[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">New Passwo rd Confirmation</label>
                  <input type="password" :disabled="isLoading"
                    :class="['form-control', { 'is-invalid': errors.new_password_confirmation }]"
                    v-model="form.new_password_confirmation" />
                  <span v-if="errors.new_password_confirmation" class="invalid-feedback"> {{
                    errors.new_password_confirmation }}
                  </span>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary px-3" form="formPassword" :disabled="isLoading">
              <i v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></i>
              <i v-else class="bi bi-floppy" style="padding-right: 3px;"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <div id="print_invoice" class="d-none">
      <div style="text-align: center;">
        <img src="images/sourkea.png" height="100px" width="250px" />
        <i style="font-size: 11px; display: block">
          Address: #33, Steet 99, Boeung Trabek, Chamkar Mon, Phnom Penh. Tel: 069 868
          768, 078 551 115
        </i>
        <h1 style="padding: 0px; margin: 0px; font-size: 30px">Invoice</h1>
      </div>
      <hr style="margin-top: 0px; padding-top: 0px" />
      <table style="width: 100%; font-size: 12px">
        <thead>
          <tr>
            <td width="80px" style="text-align: right">Table No:</td>
            <td style="text-align: left">{{ order?.name }}</td>
            <td width="80px" style="text-align: right">Invoice #:</td>
            <td style="text-align: left">{{ order?.invoice_no }}</td>
          </tr>
          <tr>
            <td style="width: 60px; text-align: right">Cashier:</td>
            <td style="text-align: left; width: 100px">{{ order?.operator?.username }}</td>
            <td style="width: 60px; text-align: right">Date:</td>
            <td style="text-align: left; width: 100px">{{ dateFormat(order?.updated_at) }}</td>
          </tr>
        </thead>
      </table>
      <table style="width: 100%; margin-top: 10px" border="0" cellspacing="0" cellpadding="2px">
        <thead>
          <tr style="background: darkgray">
            <th width="20px">No</th>
            <th style="text-align: left">Description</th>
            <th style="width: 8%; text-align: center">Qty</th>
            <th style="width: 16%; text-align: right">U.P ($)</th>
            <th style="width: 12%; text-align: right">Disc (%)</th>
            <th style="width: 18%; text-align: right">Total ($)
            </th>
          </tr>
        </thead>
        <tbody>
          <tr style="font-size: 11px" v-for="(data, index) in order?.order_detail_temps">
            <td align="center">{{ index + 1 }}</td>
            <td align="left">{{ data.description }}</td>
            <td align="center">{{ data.qty }}</td>
            <td align="right">{{ numberFormat(data.unit_price) }}</td>
            <td align="right">{{ data.discount }}</td>
            <td align="right">
              {{ numberFormat(data.unit_price * data.qty * (1 - data.discount / 100)) }}
            </td>
          </tr>
        </tbody>
      </table>
      <hr />
      <table style="font-size: 14px; width: 100%;">
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
              {{ numberFormat((order.total * order.discount) / 100, 2) }}
            </td>
          </tr>
          <tr>
            <th style="text-align: right">Total ($) :</th>
            <th style="text-align: right; width: 100px;">{{ numberFormat(order?.net_amount) }}</th>
          </tr>
        </tbody>
      </table>
    </div>

    <div id="print_receipt" class="d-none">
      <div style="text-align: center">
        <img src="images/sourkea.png" height="100px" width="250px" />
        <i style="display: block">
          Address: #33, Steet 99, Boeung Trabek, Chamkar Mon, Phnom Penh. Tel: 069 868
          768, 078 551 115
        </i>
        <h1 style="padding: 0px; margin: 0px; font-size: 30px">Receipt</h1>
      </div>
      <hr style="margin-top: 0px; padding-top: 0px" />
      <table style="width: 100%">
        <thead>
          <tr>
            <td width="80px" style="text-align: right">Table No:</td>
            <td style="text-align: left">{{ order?.table?.name }}</td>
            <td width="80px" style="text-align: right">Invoice #:</td>
            <td style="text-align: left">{{ order?.invoice_no }}</td>
          </tr>
          <tr>
            <td style="width: 60px; text-align: right">Cashier:</td>
            <td style="text-align: left; width: 100px">{{ order?.operator?.username }}</td>
            <td style="width: 60px; text-align: right">Date:</td>
            <td style="text-align: left; width: 100px">{{ dateFormat(order?.created_at) }}</td>
          </tr>
        </thead>
      </table>
      <table style="width: 100%; margin-top: 10px;" border="0" cellspacing="0" cellpadding="2px">
        <thead>
          <tr style="background: darkgray">
            <th width="20px">No</th>
            <th style="text-align: left">Description</th>
            <th style="width: 8%; text-align: center">Qty</th>
            <th style="width: 16%; text-align: right">U.P ($)</th>
            <th style="width: 12%; text-align: right">Disc (%)</th>
            <th style="width: 18%; text-align: right">Total ($)
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(data, index) in order?.order_details">
            <td align="center">{{ index + 1 }}</td>
            <td align="left">{{ data.description }}</td>
            <td align="center">{{ data.qty }}</td>
            <td align="right">{{ numberFormat(data.unit_price) }}</td>
            <td align="right">{{ data.discount }}</td>
            <td align="right">
              {{ numberFormat(data.unit_price * data.qty * (1 - data.discount / 100)) }}
            </td>
          </tr>
        </tbody>
      </table>
      <hr />
      <table style="width: 100%;">
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
      <hr />
      <div style="text-align: center">
        <i style="font-size: 12px">Thank you, see you again!</i><br />
      </div>
    </div>

    <div class="fade modal" ref="tableModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header py-1 text-bg-secondary">
            <h4 class="modal-title" style="font-weight: bold">Select Table</h4>
          </div>
          <div class="modal-body">
            <div class="row row-cols-3 row-cols-sm-4 row-cols-xl-5 g-2">
              <div v-for="data in arrayTable">
                <div class="p-3 fs-2 text-center fw-bold w-100" :class="getStatus(data.status)" style="cursor: pointer"
                  @click="selectTable(data.id)">
                  {{ data.name }}
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="px-2 py-1 text-bg-secondary" style="text-align: right">Free</div>
            <div class="px-2 py-1 text-bg-danger" style="text-align: right">Busy</div>
            <div class="px-2 py-1 text-bg-success" style="text-align: right">Printed</div>
          </div>
        </div>
      </div>
    </div>
    <div class="fade modal" ref="paymentModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header py-1 text-bg-secondary">
            <h4 class="modal-title" style="font-weight: bold">Make Payment</h4>
          </div>
          <div class="modal-body p-2" style="background: white">
            <table style="width: 100%" cellspacing="0" cellpadding="2px" class="table mb-0">
              <thead>
                <tr class="table-dark">
                  <th width="20px">No</th>
                  <th style="text-align: left">Description</th>
                  <th style="width: 8%; text-align: center">Qty</th>
                  <th style="width: 16%; text-align: right">U.P ($)</th>
                  <th style="width: 15%; text-align: right">Disc (%)</th>
                  <th style="width: 18%; text-align: right">Total ($)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(data, index) in order?.order_detail_temps">
                  <td align="center">{{ index + 1 }}</td>
                  <td align="left">{{ data.description }}</td>
                  <td align="center">{{ data.qty }}</td>
                  <td align="right">{{ numberFormat(data.unit_price) }}</td>
                  <td align="center">{{ data.discount }}</td>
                  <td align="right">{{ numberFormat(data.unit_price * data.qty * (1 - data.discount / 100)) }}</td>
                </tr>
              </tbody>
            </table>
            <table class="mt-2" style="font-size: 14px; width: 100%;" cellpadding="5px">
              <tbody>
                <tr v-if="order && order.discount > 0">
                  <td style="text-align: right"> Discount ({{ order.discount }}%) : </td>
                  <td style="text-align: right;"> {{ numberFormat((order.total * order.discount) / 100) }} </td>
                </tr>
                <tr>
                  <th style="text-align: right">Total ($) : </th>
                  <th style="text-align: right; width: 100px;">{{ numberFormat(order?.total) }}</th>
                </tr>
                <tr>
                  <td style="text-align: right">Total ($) : </td>
                  <td style="text-align: right">{{ numberFormat(order?.net_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer" style="display: block">
            <div class="row">
              <div class="col-md-8">
                <label class="form-label required">Receive Amount</label>
                <span v-if="receive_amount_error" class="text-danger"> {{ receive_amount_error }} </span>
                <div class="row">
                  <div class="col-md-6">
                    <div class="input-group mb-3">
                      <span class="input-group-text">$</span>
                      <input type="number" class="form-control" v-model="receive_amount"
                        @keydown.enter="confirmPayment()" ref="autofocus1" />
                      <div class="input-group-append">
                        <button class="btn btn-success" style="border-radius: 0px"
                          @click="receive_amount = numberFormat(order?.net_amount)">
                          <i class="bi bi-check-lg"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="text-end align-text-bottom pt-lg-4">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel</button>&nbsp;
                  <button type="button" class="btn btn-primary" @click="confirmPayment()" :disabled="isLoading">
                    {{ isLoading ? 'Processing' : 'Confirm' }}
                  </button>
                </div>
              </div>
            </div>
            <!-- <div class="row">
              <div class="col-md-12  text-danger" v-if="errorMsg">
                {{ errorMsg }}
              </div>
            </div> -->
          </div>
        </div>
      </div>
    </div>
    <header id="header" class="header fixed-top d-flex align-items-center">

      <div class="d-flex align-items-center justify-content-between">
        <a href="/" class="logo d-flex align-items-center">
          <img src="images/logo.png" alt="">
        </a>
      </div>

      <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
          <li class="d-none d-md-inline-block form-inline ms-auto nav-item dropdown me-5">
            <i class="bi bi-alarm-fill text-secondary pe-2"></i>
            <span class="text-secondary">{{ auth?.user?.server_time }}</span>
          </li>
          <li class="nav-item dropdown pe-3">
            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-fill" style="font-size: 35px;"></i>
              <span class="d-none d-md-block dropdown-toggle ps-2 text-capitalize">{{ auth?.user?.username }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
              <li>
                <button class="dropdown-item d-flex align-items-center" @click="openModal">
                  <i class="bi bi-shield-lock"></i>
                  <span>Change Password</span>
                </button>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>

              <li>
                <button type="submit" class="dropdown-item d-flex align-items-center" @click="auth.logout()">
                  <i class="bi bi-box-arrow-right"></i>
                  <span>Sign Out</span>
                </button>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </header>

    <main id="main" class="main ms-0 pt-3">
      <div class="row">
        <div class="col-xl-7 col-lg-6">
          <div class="card mb-0" style="height: 88vh">
            <ul class="nav nav-tabs nav-fill">
              <li class="nav-item">
                <span class="nav-link menu-item active" style="cursor: pointer" @click="menuByCateId(0)"
                  id="cate_0">ALL</span>
              </li>
              <li class="nav-item" v-for="data in categoryList">
                <span class="nav-link menu-item" style="cursor: pointer" @click="menuByCateId(data.id)"
                  :id="'cate_' + data.id">{{ data.name }}</span>
              </li>
              <li class="nav-item">
                <span class="nav-link menu-item" style="padding: 5px; background: whitesmoke !important">
                  <input type="text" class="form-control" placeholder="Search..." v-model="search"
                    style="background: lightyellow" />
                </span>
              </li>
            </ul>
            <div class="card-body p-1" style="overflow-y: scroll">
              <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-1">
                <div class="col" v-for="data in productList" style="cursor: pointer" @click="addToOrder(data.id)">
                  <div class="card h-100 mb-0"
                    :style="data.qty && data.qty > 0 ? 'background: lightcyan' : 'background: white'">
                    <div class="card-img-top" :style="getProductImage(data.image)"></div>
                    <div class="card-body" style="font-size: 14px; padding: 3px">
                      <p class="card-text text-center mb-1">{{ data.name }}</p>
                      <span style="float: right" class="badge text-bg-success" v-if="data.qty && data.qty > 0">{{
                        data.qty
                      }}</span>
                      <p class="card-text text-center mb-1" style="color: red">
                        {{ currencyFormat(data.unit_price) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-5 col-lg-6">
          <div class="card mb-0" style="height: 88vh">
            <div class="card-header p-2 d-none d-lg-block">
              <div class="row row-cols-4 g-1">
                <div>
                  <button class="btn btn-secondary w-100" @click="showTable()" title="Select Table">
                    {{ order?.name ?? "Table" }}
                  </button>
                </div>
                <div>
                  <button :disabled="!(checkList && checkList.length > 0)" title="Change Table"
                    class="btn btn-primary w-100" @click="showTable(table_id)">Change</button>
                </div>
                <div>
                  <button class="btn btn-warning w-100" title="Print"
                    :disabled="!order || order?.order_detail_temps?.length == 0" @click="printInvoice()">Print</button>
                </div>
                <div>
                  <button class="btn btn-success w-100" title="Payment"
                    :disabled="!order || order?.order_detail_temps?.length == 0" @click="makePayment">Payment</button>
                </div>
              </div>
            </div>
            <div class="card-body p-0 cashier-menu" style="overflow-y: scroll">
              <table class="table">
                <thead>
                  <tr class="table-dark">
                    <th style="width: 10px;padding: 5px !important;" class="pb-1">
                      <input type="checkbox"
                        :checked="checkList && checkList.length == order?.order_detail_temps.length"
                        v-if="order?.order_detail_temps && order?.order_detail_temps.length > 0"
                        :indeterminate="checkList && checkList.length > 0 && checkList.length < order?.order_detail_temps.length"
                        style="width: 18px; height: 18px; margin-top: 3px" @change="checkAll($event)" />
                    </th>
                    <th style="padding: 5px !important;">Desc</th>
                    <th style="width: 50px;padding: 5px !important;">QTY</th>
                    <th class="text-end" style="width: 90px;padding: 5px !important;"> U.P ($) </th>
                    <th class="text-end" style="width: 70px;padding: 5px !important;">DC(%)</th>
                    <th class="text-end" style="width: 100px;padding: 5px !important;"> Total ($) </th>
                    <th style="width: 10px;padding: 5px !important;"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="data in order?.order_detail_temps">
                    <td class="pb-0" style="padding: 5px !important;">
                      <input type="checkbox" :id="data.id" :value="data.id" v-model="checkList"
                        style="width: 18px; height: 18px; margin-top: 3px" />
                    </td>
                    <td style="padding: 5px !important;"> {{ data.description }} </td>
                    <td style="padding: 5px !important;">
                      <input type="number" min="1" style="border: none; appearance: none; background: #e9ecef;"
                        class="form-control p-0 text-center" @change="updateQty($event, data.id)" v-model="data.qty" />
                    </td>
                    <td class="text-end" style="padding: 5px !important;">{{ numberFormat(data.unit_price, 2) }}
                    </td>
                    <td style="padding: 0 10px !important;" class="text-end">
                      <input type="number" min="0" style="border: none; appearance: none;background: #e9ecef;"
                        class="form-control p-0 text-center" v-model="data.discount"
                        @change="updateDetailDiscount($event, data.id)" />
                    </td>
                    <td class="text-end" style="padding: 5px !important;">
                      {{ numberFormat(data.unit_price * data.qty * (1 - data.discount / 100), 2) }}
                    </td>
                    <td style="padding: 5px !important;">
                      <i class="bi bi-trash" style="color: red; cursor: pointer" @click="deleteData(data.id)"></i>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="card-footer p-1 text-dark" style="background: whitesmoke" v-if="order">
              <table class="table mb-0" style="background: whitesmoke">
                <tbody>
                  <tr>
                    <td class="text-end" style="width: 50px">Discount (%) :</td>
                    <td style="width: 100px">
                      <input type="number" min="0"
                        style="border: none; appearance: none;background: #e9ecef;max-width: 50px"
                        class="form-control p-0 text-center w-100" @change="updateOrderDiscount($event)"
                        v-model="order.discount" />
                    </td>
                    <th class="text-end" style="width: 100px">Total ($) :</th>
                    <th class="text-end text-danger" style="width: 50px;">{{ numberFormat(order.total, 2) }}</th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
<script setup>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { clearForm, currencyFormat, dateFormat, numberFormat, setFocus } from '../../helper.js';
import { Modal } from 'bootstrap';
import ShareModal from '../Share/Modal.vue';
import printJS from 'print-js'
import { useAuthStore } from '@/store/auth';
const formModalInstance = ref(null);
const formModal = ref(null);
const autofocus = ref(null);
const autofocus1 = ref(null);
const messageBox = ref(null);
const auth = useAuthStore();
const categoryList = ref([]);
const productList = ref([]);
const productListBase = ref([]);
const arrayTable = ref([]);
const search = ref("");
const categoryId = ref(0);
const tableModal = ref(null);
const tableModalInstance = ref(null);
const paymentModal = ref(null);
const paymentModalInstance = ref(null);
const deleteModal = ref(null);
const deleteModalInstance = ref(null);
const table_id = ref(0);
const old_cate_id = ref(0);
const checkList = ref([]);
const isLoading = ref(false);
const errors = ref({});
const receive_amount_error = ref(null);
const order = ref({});
const form = ref(
  {
    old_password: null,
    new_password: null,
    new_password_confirmation: null
  }
);

const receive_amount = ref(0);

onMounted(() => {
  document.body.style.display = "block";

  if (formModal.value) {
    formModalInstance.value = new Modal(formModal.value);
    formModal.value.addEventListener("shown.bs.modal", () => {
      setFocus(autofocus);
    });
    formModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
    formModal.value.addEventListener("hidden.bs.modal", () => {
      clearForm(form.value);
      errors.value = {};
    });
  }

  if (tableModal.value) {
    tableModalInstance.value = new Modal(tableModal.value);
    tableModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
  }

  if (deleteModal.value) {
    deleteModalInstance.value = new Modal(deleteModal.value);
    deleteModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
  }

  if (paymentModal.value) {
    paymentModalInstance.value = new Modal(paymentModal.value);
    paymentModal.value.addEventListener("shown.bs.modal", () => {
      setFocus(autofocus1);
    });
    paymentModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
    paymentModal.value.addEventListener("hidden.bs.modal", () => {
      clearForm(form.value);
      receive_amount_error.value = null;
    });
  }

  getData();
  table_id.value = sessionStorage.getItem("table_id");
  selectTable(table_id.value);
});


onUnmounted(() => {
  if (formModalInstance.value) {
    formModalInstance.value.dispose();
  }
  if (tableModalInstance.value) {
    tableModalInstance.value.dispose();
  }
  if (deleteModalInstance.value) {
    deleteModalInstance.value.dispose();
  }
  if (paymentModalInstance.value) {
    paymentModalInstance.value.dispose();
  }
});

// load data
const getData = () => {
  isLoading.value = true;
  axios.get("api/cashier").then((response) => {
    if (response.data.success) {
      categoryList.value = response.data.product_categories;
      productList.value = response.data.products;
      productListBase.value = response.data.products;
    }
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};
// add or create
const openModal = () => {
  formModalInstance.value.show();
};

// submit form
const changePassword = () => {
  isLoading.value = true;
  axios.post("api/auth/change-password", form.value)
    .then((response) => {
      if (response.data.success) {
        formModalInstance.value.hide();
        messageBox.value.showModal(1, null, null, 'Your password has been changed successfully');
      } else {
        errors.value = response.data.errors;
        setFocus(autofocus);
      }
    })
    .catch((ex) => {
      console.log(ex);
      setFocus(autofocus);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const menuByCateId = (id) => {
  categoryId.value = id;
  filterMenu();
  if (id != old_cate_id.value) {
    document.getElementById("cate_" + old_cate_id.value).classList.remove("active");
    document.getElementById("cate_" + id).classList.add("active");
    old_cate_id.value = id;
  }
};

// Filter Menu by its category
const filterMenu = () => {
  if (categoryId.value == 0) productList.value = productListBase.value;
  else
    productList.value = productListBase.value.filter(
      (v) => v.product_category_id == categoryId.value
    );
  if (search.value) {
    productList.value = productList.value.filter((v) => v.name.toLowerCase().includes(search.value.toLowerCase()));
  }
};

// get table status
const getStatus = (status) => {
  if (status == 2) return "text-bg-secondary";
  else if (status == 1) return "text-bg-danger";
  else return "text-bg-success";
};

// show table list
const showTable = (id = 0) => {
  arrayTable.value = [];
  isLoading.value = true;
  axios.get("api/cashier/show-table/" + id)
    .then((response) => {
      if (response.data.success) {
        Object.assign(arrayTable.value, response.data.data);
        tableModalInstance.value.show();
      }
    })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// select table
const selectTable = (id) => {
  axios.post("api/cashier/select-table", {
    old_table_id: table_id.value,
    new_table_id: id,
    ids: JSON.stringify(checkList.value)
  })
    .then((response) => {
      if (response.data.success) {
        table_id.value = id;
        order.value = response.data.data;
        sessionStorage.setItem("table_id", id);
        checkList.value = [];
        tableModalInstance.value.hide();
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// add product to order list
const addToOrder = (id) => {
  if (isNaN(table_id.value) || parseInt(table_id.value) == 0) {
    messageBox.value.showModal(2, null, null, 'Please select any table first');
    return;
  }
  axios.post("api/cashier/add-to-order", {
    table_id: table_id.value,
    product_id: id
  })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

const getProductImage = (image) => {
  if (image)
    return "background: url('storage/" + image + "') no-repeat center; height:80px";
  else
    return "background: url('./images/default.png') no-repeat center; height:80px";
};

// delete
const deleteData = (id) => {
  messageBox.value.showModal(4, () => {
    axios.delete("api/cashier/delete-order/" + id + "/" + table_id.value).then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
      .catch((ex) => {
        console.log(ex);
      });
  });
};

// update order quantity
const updateQty = (e, id) => {
  axios.post("api/cashier/update-order-qty", {
    id: id,
    qty: e.target.value,
    table_id: table_id.value,
  })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// update order detail discount
const updateDetailDiscount = (e, id) => {
  axios.post("api/cashier/update-detail-discount", {
    id: id,
    discount: e.target.value,
    table_id: table_id.value
  })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// update main order discount
const updateOrderDiscount = (e) => {
  axios.post("api/cashier/update-order-discount", {
    discount: e.target.value,
    table_id: table_id.value
  })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// check all order items
const checkAll = (e) => {
  if (e.target.checked) {
    order.value.order_detail_temps.forEach((v) => {
      if (v.id) checkList.value.push(v.id);
    });
  } else {
    checkList.value = [];
  }
};

// print order invoice
const printInvoice = () => {
  printJS({
    printable: "print_invoice",
    type: "html",
    scanStyles: false,
    style: "#print_invoice{ display: block !important; }"
  });
};

// make payment
const makePayment = () => {
  paymentModalInstance.value.show();
};

// confirm payment
const confirmPayment = () => {
  isLoading.value = true;
  axios.post("api/cashier/confirm-payment", {
    table_id: table_id.value,
    receive_amount: receive_amount.value
  })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
        paymentModalInstance.value.hide();
        setTimeout(() => {
          printJS({
            printable: "print_receipt",
            type: "html",
            scanStyles: false,
            style: "#print_receipt{ display: block !important; }"
          });
          order.value = null;
        }, 10);
      } else {
        if (response.data.errors && response.data.errors.receive_amount)
          receive_amount_error.value = response.data.errors.receive_amount[0];
        setFocus(autofocus1);
      }
    })
    .catch((ex) => {
      console.log(ex);
      setFocus(autofocus1);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// Watch
watch(() => search.value, (newValue, oldValue) => {
  filterMenu();
});

</script>