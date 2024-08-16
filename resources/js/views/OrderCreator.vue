<template>
  <div class="bg-tame-gray">
    <div class="row gx-1">
      <div class="col-lg-8 col-md-7">
        <div class="bg-white mx-2 mt-1 rounded-xl">
          <div
            :class="`order-creator border-bottom ${
              !searchBarOpen ? 'px-2 py-2' : 'px-2 py-2'
            }`"
          >
            <div class="py-2 search-bar px-4 rounded-2" v-if="searchBarOpen">
              <div class="d-flex w-100 flex-nowrap align-items-center">
                <div class="search-icon">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    x="0px"
                    y="0px"
                    viewBox="0 0 56.966 56.966"
                    style="enable-background: new 0 0 56.966 56.966"
                    xml:space="preserve"
                    width="100%"
                    class="sm-search-svg"
                  >
                    <path
                      d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23  s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92  c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17  s-17-7.626-17-17S14.61,6,23.984,6z"
                      fill="#FFFFFF"
                    />
                  </svg>
                </div>
                <div class="search-field w-100 px-3">
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Search for an item by its name"
                    v-model="searchKey"
                    id="search-field"
                  />
                </div>
                <div class="search-cancel-btn ml-auto">
                  <a href="javascript:void(0)" @click="hideSearchBar">
                    Cancel
                  </a>
                </div>
              </div>
            </div>
            <div
              class="d-block d-lg-flex w-100 justify-content-center align-items-center"
              v-else
            >
              <h4 class="mb-1 text-dark fw-bolder d-lg-none">Menu</h4>
              <div
                class="d-flex mb-2 d-lg-none w-100 justify-content-center align-items-center"
              >
                <form class="px-0">
                  <div class="search-box">
                    <div class="position-relative">
                      <input
                        type="search"
                        class="form-control rounded rounded-xl border-1"
                        placeholder="Search"
                        v-model="searchKey"
                      /><i class="uil uil-search search-icon"></i>
                    </div>
                  </div>
                </form>
                <div
                  class="text-primary ms-1 border rounded fw-bolder"
                  style="padding: 0.1rem 0.25rem"
                >
                  <a
                    href="javascript:void(0)"
                    data-bs-toggle="collapse"
                    data-bs-target="#categoriesFilters"
                    style="padding: 0.01rem 0.16rem"
                    ><i class="uil uil-filter fw-bold icon fs-1"></i
                  ></a>
                </div>
              </div>
              <div class="top-left d-none d-lg-block">
                <h4 class="mb-0">Create Order</h4>
                <span class="text-muted">Select Food items</span>
              </div>
              <div
                class="top-right ms-auto d-flex align-items-center items-group collapse"
                id="categoriesFilters"
              >
                <label
                  for="all-items"
                  :class="{ active: itemsFilter.group == 'all' }"
                  class="cursor-pointer food-group d-flex align-items-center border rounded-25 me-2 p-1 pe-0"
                >
                  <avatar name="All" width="25px" height="25px"></avatar>
                  <h6 class="mb-0 px-2">All</h6>
                  <div class="form-check">
                    <input
                      id="all-items"
                      class="form-check-input"
                      type="radio"
                      v-model="itemsFilter.group"
                      value="all"
                      name="menu_group"
                    />
                  </div>
                </label>
                <label
                  for="food-items"
                  :class="{ active: itemsFilter.group == 'foods' }"
                  class="cursor-pointer food-group d-flex align-items-center border rounded-25 me-2 p-1 pe-0"
                >
                  <avatar name="Food" width="25px" height="25px"></avatar>
                  <h6 class="mb-0 px-2">Food Items</h6>
                  <div class="form-check">
                    <input
                      id="food-items"
                      class="form-check-input"
                      type="radio"
                      v-model="itemsFilter.group"
                      value="foods"
                      name="menu_group"
                    />
                  </div>
                </label>
                <label
                  for="drink-items"
                  :class="{ active: itemsFilter.group == 'drinks' }"
                  class="cursor-pointer food-group d-flex align-items-center border rounded-25 p-1 pe-0 me-2"
                >
                  <avatar name="Drinks" width="25px" height="25px"></avatar>
                  <h6 class="mb-0 px-2">Beverages</h6>
                  <div class="form-check">
                    <input
                      id="drink-items"
                      class="form-check-input"
                      type="radio"
                      v-model="itemsFilter.group"
                      value="drinks"
                      name="menu_group"
                    />
                  </div>
                </label>
                <a
                  href="javascript:void(0)"
                  class="text-dark pos-link d-none d-lg-block"
                  @click.prevent="showSearch"
                >
                  <i class="demo-pli-magnifi-glass fs-3 fw-bolder"></i>
                </a>
              </div>
            </div>
          </div>
          <div
            class="categories-carousel w-100 py-2 px-2 border-bottom d-none d-lg-block"
            v-if="$helper.empty(itemsFilter.category) && !searchBarOpen"
          >
            <swiper
              :navigation="true"
              :modules="modules"
              :slides-per-view="6"
              :space-between="3"
            >
              <swiper-slide
                v-for="(item, i) in categories"
                :key="'categories' + i"
              >
                <a
                  href="javascript:void(0)"
                  class="d-flex flex-column text-center menu-carousel-item bg-white border shadow-sm p-2 rounded"
                  @click.prevent="setCurrentCategory(item)"
                >
                  <div class="mt-0">
                    <span class="text-truncate h6 text-capitalize">{{
                      getGeneralName(item.name)
                    }}</span>
                  </div>
                </a>
              </swiper-slide>
            </swiper>
          </div>
          <div
            class="category-breadcumb w-100 py-2 px-1 border-bottom"
            v-else-if="!$helper.empty(itemsFilter.category)"
          >
            <div class="py-3 px-4">
              <div class="d-flex flex-nowrap align-items-center">
                <button
                  type="button"
                  class="btn btn-icon btn-primary rounded-circle"
                  @click="
                    () => {
                      itemsFilter.category = null;
                    }
                  "
                >
                  <svg
                    height="18px"
                    class="icon-lg fs-5"
                    viewBox="0 0 16 16"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"
                    ></path>
                  </svg>
                </button>
                <div class="current-category ms-4 text-capitalize">
                  <h4 class="mb-0">{{ itemsFilter.category.name }}</h4>
                  <p class="lead mb-0">Discover whatever you need easily.</p>
                </div>
                <nav aria-label="breadcrumb" class="ms-auto">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <a href="javascript:void(0)">Menu</a>
                    </li>
                    <li class="breadcrumb-item">
                      <a href="javascript:void(0)">{{ itemsFilter.group }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                      {{ itemsFilter.category.name }}
                    </li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <!-- Items List containger-->
        <div
          class="items-list scrollable-content full w-100 py-2 ps-2 border-bottom"
        >
          <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gx-1">
            <a
              href="javascript:void(0)"
              @click.prevent="addToCart(item)"
              class="col"
              v-for="item in products"
              :key="item.id"
            >
              <div
                class="border mb-2 rounded-xl py-3 px-2 border-2 bg-white"
                :class="{ 'added-item': cartItems.includes(item.id) }"
              >
                <div class="d-flex align-items-center w-100">
                  <div class="product-desc text-truncate">
                    <h6 class="mb-1 fw-bold text-truncate">{{ item.name }}</h6>
                    <span class="text-muted h6"> {{ item.type }} </span>
                  </div>
                </div>
                <div class="mt-3 d-flex align-items-center">
                  <span
                    class="m-0 fs-6 fw-border rounded-4 border p-1 px-2 text-dark"
                  >
                    {{ formatMoney(item.price) }}
                  </span>
                  <span class="ms-auto">
                    <span class="text-muted h6"
                      >&nbsp;
                      <svg
                        height="18px"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 8l4 4m0 0l-4 4m4-4H3"
                        ></path>
                      </svg>
                      {{ item.group }}</span
                    >
                  </span>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
      <!-- Right Panel-->
      <div class="col-lg-4 col-md-5 h-100" id="right-panel">
        <div class="bg-white rounded-xl mt-1 pt-1">
          <div class="d-flex border-bottom">
            <div class="col border-end">
              <div class="d-flex align-items-center p-2">
                <div class="dropdown">
                  <a
                    href="javascript:void(0)"
                    class="fw-bolder h6 m-0 text-primary"
                    data-bs-toggle="dropdown"
                  >
                    <i class="demo-pli-receipt-4 me-1"></i>
                    <span>{{ orderCategory }}</span>
                  </a>
                  <div
                    class="dropdown-menu dropdown-menu-end w-md-200px w-sm-250px"
                    data-popper-placement="bottom-end"
                  >
                    <div
                      class="list-group list-group-borderless tables-list scrollable-content"
                    >
                      <div
                        v-for="item in [
                          'DINE IN',
                          'ROOM SERVICES',
                          'TAKE AWAY',
                          'DELIVERY',
                        ]"
                        :key="item"
                        class="list-group-item list-group-item-action d-flex align-items-start mb-2"
                        :class="{
                          active: orderCategory == item,
                        }"
                        @click="
                          () => {
                            orderCategory = item;
                          }
                        "
                      >
                        <a
                          href="javascript:void(0)"
                          class="h6 d-block mb-0 stretched-link text-decoration-none"
                          >{{ item }}</a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col border-end" v-if="orderCategory == 'DINE IN'">
              <div class="d-flex align-items-center p-2">
                <div class="table-name">
                  <h5 class="mb-0 text-primary">
                    {{ selectedTable?.name || "Table" }}
                  </h5>
                </div>
                <div class="icon ms-auto">
                  <div class="dropdown">
                    <a href="javascript:void(0)" data-bs-toggle="dropdown">
                      <img
                        :src="`${publicPath}img/table_icon2.png`"
                        style="width: 22px; height: auto"
                      />
                    </a>
                    <div
                      class="dropdown-menu dropdown-menu-end w-md-200px w-sm-250px"
                      data-popper-placement="bottom-end"
                    >
                      <div class="border-bottom px-1 py-2">
                        <div class="form-group">
                          <input
                            type="text"
                            placeholder="Search for table..."
                            class="form-control"
                            autocomplete="off"
                            v-model="tableKeyword"
                          />
                        </div>
                      </div>

                      <div
                        class="list-group list-group-borderless tables-list scrollable-content"
                      >
                        <div
                          v-for="item in filteredTables"
                          :key="item.id"
                          class="list-group-item list-group-item-action d-flex align-items-start mb-2"
                          :class="{
                            'selected-table': selectedTable.id == item.id,
                            'locked-table': activeTables.includes(item.id),
                          }"
                          @click="chooseTable(item)"
                        >
                          <div class="flex-shrink-0 me-3">
                            <svg
                              height="28px"
                              class="text-success check-icon"
                              viewBox="0 0 16 16"
                              xmlns="http://www.w3.org/2000/svg"
                              fill="currentColor"
                            >
                              <path
                                fill-rule="evenodd"
                                d="M15.354 2.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L8 9.293l6.646-6.647a.5.5 0 0 1 .708 0z"
                              ></path>
                              <path
                                fill-rule="evenodd"
                                d="M8 2.5A5.5 5.5 0 1 0 13.5 8a.5.5 0 0 1 1 0 6.5 6.5 0 1 1-3.25-5.63.5.5 0 1 1-.5.865A5.472 5.472 0 0 0 8 2.5z"
                              ></path>
                            </svg>
                            <svg
                              height="28px"
                              class="text-danger cross-icon"
                              xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            >
                              <polygon
                                points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"
                              ></polygon>
                              <line x1="15" y1="9" x2="9" y2="15"></line>
                              <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                          </div>
                          <div class="flex-grow-1">
                            <a
                              href="javascript:void(0)"
                              class="h6 d-block mb-0 stretched-link text-decoration-none"
                              >{{ item.name }}</a
                            >
                            <small class="text-muted"
                              >Capacity:
                              <span class="fw-bolder">{{
                                item.capacity
                              }}</span></small
                            >
                          </div>
                        </div>

                        <div class="text-center mb-2">
                          <a href="javascript:void(0)" class="btn-link"
                            >Refresh tables</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div
              :class="`${
                orderCategory == 'ROOM SERVICES' ? 'col-8' : 'col-6'
              } d-flex border-end`"
            >
              <zoho-search
                url="shared/occupied-rooms"
                label="name"
                primary-key="id"
                placeholder="Select Room"
                v-model="room_id"
                :load-defaults="true"
                fetch-flag="SEARCHING_FOR_ROOMS"
                v-if="orderCategory == 'ROOM SERVICES'"
                @selected="setCustomerFromRoom"
              >
              </zoho-search>
              <zoho-search
                v-else
                url="clients/search?with_phones=1"
                label="name"
                primary-key="id"
                placeholder="Customer"
                v-model="customer"
                :load-defaults="true"
                fetch-flag="SEARCHING_FOR_CLIENTS"
              >
                <template #footer>
                  <div class="position-sticky bottom-0 bg-white">
                    <li class="border-top dropdown-item pl-7">
                      <a
                        href="javascript:void(0)"
                        @click.prevent="newClientModalOpen = true"
                      >
                        <span class="text-blue cursor-pointer"
                          ><svg
                            height="18px"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          >
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                          </svg>
                          &nbsp;Add New</span
                        >
                      </a>
                    </li>
                  </div>
                </template>
              </zoho-search>
            </div>
          </div>
          <div
            class="pb-lg-5 pb-5 cart-container scrollable-content"
            v-if="orderItems.length"
          >
            <div class="table-responsives">
              <table class="table table-striped table-sm">
                <thead>
                  <th>Item</th>
                  <th class="d-none d-md-block">Price</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th></th>
                </thead>
                <tbody>
                  <template v-for="(item, i) in orderItems" :key="i">
                    <tr>
                      <td>
                        <div class="item">
                          <a
                            data-bs-toggle="collapse"
                            :href="'#item-more' + i"
                            class="d-flex align-items-center w-100"
                          >
                            <span
                              class="border p-1-1 rounded-circle text-muted me-1"
                            >
                              <svg
                                height="18px"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                              >
                                <path
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5l7 7-7 7"
                                ></path>
                              </svg>
                            </span>
                            <span
                              class="badge rounded bg-success p-1 me-1"
                              v-if="
                                item.old_quantity &&
                                item.old_quantity != item.quantity
                              "
                            >
                              <span></span>
                            </span>
                            <div class="d-block">
                              <h6 class="mb-0">
                                <span class="text-truncate">{{
                                  item.name
                                }}</span>
                              </h6>
                            </div>
                          </a>
                        </div>
                      </td>
                      <td style="width: 100px" class="d-none d-md-block">
                        <input
                          v-if="canChangePrice"
                          type="text"
                          class="form-control form-control-sm py-1 px-1"
                          v-model="orderItems[i].price"
                        />

                        <h6 class="mb-0" v-else>
                          {{ formatMoney(item.price || item.cost) }}
                        </h6>
                      </td>
                      <td>
                        <div class="dz-stepper small-stepper border-2">
                          <div
                            class="input-group bootstrap-touchspin bootstrap-touchspin-injected"
                          >
                            <span class="input-group-btn input-group-prepend">
                              <button
                                class="btn btn-info bootstrap-touchspin-down"
                                type="button"
                                @click="editItemCart(item, '-', i)"
                                :disabled="item.quantity <= item.old_quantity"
                                v-if="item.old_quantity || item.round_key"
                              >
                                -
                              </button>

                              <button
                                class="btn btn-info bootstrap-touchspin-down"
                                type="button"
                                @click="addToCart(item, '-', i)"
                                :disabled="this.$helper.empty(item)"
                                v-else
                              >
                                -
                              </button> </span
                            ><input
                              class="stepper form-control"
                              type="text"
                              v-model="item.quantity"
                            /><span class="input-group-btn input-group-append">
                              <button
                                class="btn btn-primary bootstrap-touchspin-up"
                                type="button"
                                @click="
                                  item.old_quantity || item.round_key
                                    ? editItemCart(item, 'i', i)
                                    : addToCart(item, '+', i)
                                "
                              >
                                +
                              </button>
                            </span>
                          </div>
                        </div>
                      </td>
                      <td>
                        <h6 class="mb-0">
                          {{
                            $helper.formatNumber(
                              (item.price || item.cost) * item.quantity
                            )
                          }}
                        </h6>
                      </td>
                      <td>
                        <a
                          v-if="
                            !item.old_quantity ||
                            !item.round_key ||
                            hasPermissionTo('D')
                          "
                          class="text-primary"
                          href="javascript:void(0)"
                          @click.prevent="removeFromCart(i)"
                          ><i class="demo-pli-trash fs-5"></i
                        ></a>
                      </td>
                    </tr>
                    <tr
                      class="collapse collapse-animation"
                      :id="'item-more' + i"
                    >
                      <td colspan="5">
                        <div class="py-1 ps-2">
                          <div class="border-start ps-2">
                            <div class="form-group">
                              <label for="">Notes</label>
                              <input
                                v-model="orderItems[i].comment"
                                class="form-control"
                                placeholder="Comment"
                              />
                            </div>
                            <div class="widget widget-categories my-2 py-1">
                              <ul :id="'addons-' + i">
                                <li class="has-children">
                                  <a
                                    class="d-flex align-items-center"
                                    href="javascript:void(0)"
                                    aria-expanded="true"
                                  >
                                    <span>Add-Ons</span>
                                    <span class="badge text-muted ms-auto"
                                      ><i
                                        class="demo-pli-pen-5 fs-5"
                                        @click.self="
                                          () => {
                                            selectedItemIndex = i;
                                            selectedAddons = [...item.addons];
                                            toggleAddonsModal();
                                          }
                                        "
                                      ></i></span
                                  ></a>
                                  <ul
                                    class="collapse show"
                                    style="background: #f9fafc"
                                    v-if="item.addons && item.addons.length"
                                  >
                                    <li
                                      v-for="(row, k) in item.addons"
                                      :key="i + 'addon' + k"
                                    >
                                      <a href="#" class="text-foggy fw-normal"
                                        >{{ row.name
                                        }}<span class="badge text-muted ms-1"
                                          ><i
                                            class="demo-pli-trash fs-6 fw-bold text-danger"
                                          ></i></span
                                      ></a>
                                    </li>
                                  </ul>
                                </li>
                              </ul>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
            <div
              class="position-sticky w-100 bottom-0"
              v-if="orderItems.length"
              style="z-index: 99"
            >
              <div class="card border shadow-none rounded-0 rounded-top">
                <div class="p-2 pt-3">
                  <h5 class="d-flex align-items-center w-100 mb-0">
                    <span class="fw-border">Payable Amount:</span>
                    <span class="text-primary ms-auto">
                      {{ formatMoney(subTotal) }}
                    </span>
                  </h5>
                </div>
                <div
                  class="pt-1 pb-2 px-2"
                  v-if="appSettings.print_round_slip == 1"
                >
                  <div class="form-check">
                    <input
                      id="_dm-rememberCheck3"
                      class="form-check-input"
                      type="checkbox"
                      v-model="print_round_slip"
                    />
                    <label for="_dm-rememberCheck3" class="form-check-label">
                      Print Round Total Slip
                    </label>
                  </div>
                </div>
                <div class="d-flex w-100 align-items-center">
                  <div
                    class="w-50 px-1 pb-2 d-none d-lg-block"
                    v-if="isCashier"
                  >
                    <div class="form-group">
                      <select
                        class="form-control-lg form-select"
                        v-model="waiter"
                        @change="selectWaiter($event)"
                      >
                        <option value="null" hidden disabled>
                          Select Waiter
                        </option>
                        <template v-for="waiter in waiters">
                          <option
                            :value="waiter.id"
                            :key="waiter.id"
                            v-if="isWaiter(waiter)"
                          >
                            {{ waiter.name }}
                          </option>
                        </template>
                      </select>
                    </div>
                  </div>
                  <button
                    class="w-50 btn btn-danger btn-lg d-block d-md-none mx-2"
                    type="button"
                    @click="handleCancel"
                  >
                    Cancel
                  </button>
                  <wolf-button
                    class="w-50 btn btn-primary btn-lg me-2"
                    activator="PLACING_ORDER"
                    :disabler="submitDisabled"
                    @clicked="handleSubmit"
                  >
                    Place Order
                  </wolf-button>
                </div>
              </div>
            </div>
          </div>
          <div class="cart-container justify-content-center" v-else>
            <empty-results>
              <template #description
                ><span>No items added on order list.</span></template
              >
            </empty-results>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal for Addons-->
  <bootstrap-modal
    v-if="addonsModalOpen"
    @close="toggleAddonsModal"
    modal-size="modal-default"
  >
    <template #head>
      <span>Select Addons</span>
    </template>
    <template #body>
      <div class="w-100">
        <div class="orders-container">
          <div class="order-item" v-for="(addon, i) in addons" :key="addon.id">
            <div class="order-card">
              <h4 class="text-primary">{{ addon.name }}</h4>
              <div
                class="form-check mb-2"
                v-for="item in addon.items"
                :key="'addon-items' + i + item.id"
                @click="selectAddon(item)"
              >
                <input
                  :id="'addon-items' + i + item.id"
                  class="form-check-input"
                  type="checkbox"
                  :checked="currentAddons.includes(item.id)"
                />
                <label
                  :for="'addon-items' + i + item.id"
                  class="form-check-label"
                >
                  {{ item.name }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group text-end">
          <hr />
          <button
            type="button"
            class="btn btn-primary w-25"
            @click="setItemAddons"
            :disabled="!currentAddons.length"
          >
            Confrm
          </button>
        </div>
      </div>
    </template>
  </bootstrap-modal>

  <client-creator
    v-if="newClientModalOpen"
    @closed="newClientModalOpen = false"
    @saved="handleAddedClient"
  />
</template>
<script src="./order-creator.js"></script>
<style scoped>
.rounded-25 {
  border-radius: 25px !important;
}
.btn-icon.btn-xxs {
  width: calc(0.5rem + 0.75em);
  height: calc(0.5rem + 0.75em);
}
.selected-table {
  z-index: 1;
  color: #495057;
  text-decoration: none;
  background-color: #f2f4f8;
}
.search-bar {
  z-index: 3;
  background: #141543 !important;
}
.search-icon {
  width: 2rem;
}
.sm-search-svg {
  width: 20px;
}
.search-cancel-btn a {
  font-weight: bold;
  color: #fff;
  font-weight: 2rem;
}
.search-field input.form-control {
  border-radius: 4px;
  background-color: #1f3c5a !important;
  border: none;
  color: hsla(0, 0%, 100%, 0.8);
  width: 100%;
  text-align: start;
  background-color: #1f3c5a;
  font-weight: 2rem;
}
.items-list.full {
  height: calc(100vh - 8.5rem);
}
.edited-ribbon {
  position: absolute;
  left: -5px;
  top: -5px;
  z-index: 1;
  overflow: hidden;
  width: 15px;
  height: 15px;
  text-align: right;
}
.edited-ribbon span {
  font-size: 8px;
  font-weight: 600;
  color: #fff;
  text-transform: uppercase;
  text-align: center;
  line-height: 20px;
  transform: rotate(45deg);
  -webkit-transform: rotate(45deg);
  width: 100px;
  display: block;
  background: #e53f50;
  box-shadow: 0 3px 10px -5px #000;
  position: absolute;
  top: 19px;
  left: -21px;
}
.collapse-animation {
  -webkit-transition: all 0.3s ease-in-out;
  transition: all 0.3s ease-in-out;
}
.cart-icon {
  padding-bottom: 2px;
  -webkit-transform: rotate(0deg);
  transform: rotate(0deg);
  -webkit-transition: -webkit-transform 0.3s ease-in-out;
  transition: -webkit-transform 0.3s ease-in-out;
  transition: transform 0.3s ease-in-out;
  transition: transform 0.3s ease-in-out, -webkit-transform 0.3s ease-in-out;
  color: #dee2e6;
  font-size: 1.7rem;
}
.p-1-1 {
  -webkit-transition: -webkit-transform 0.3s ease-in-out;
  transition: -webkit-transform 0.3s ease-in-out;
  transition: transform 0.3s ease-in-out;
  transition: transform 0.3s ease-in-out, -webkit-transform 0.3s ease-in-out;
}
[aria-expanded="true"] .p-1-1 {
  -webkit-transform: rotate(90deg);
  transform: rotate(90deg);
}

.order-item {
  margin: 0;
  display: grid;
  grid-template-rows: 1fr auto;
  margin-bottom: 10px;
  break-inside: avoid;
}

.order-item > .order-card {
  grid-row: 1 / -1;
  grid-column: 1;
}

.orders-container {
  column-count: 2;
  column-gap: 10px;
}
</style>
