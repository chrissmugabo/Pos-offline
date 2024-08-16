<script setup>
import { ref } from 'vue';

const emits = defineEmits(["searching", "actionRequested"])
const searchKey = ref("");
</script>
<template>
  <div class="ant-transfer-list bg-white">
    <div class="ant-transfer-list-header">
      <span class="ant-transfer-list-header-selected">
        <span class="fw-bold"><slot name="title"></slot></span>
        <span class="ant-transfer-list-header-title">
          <button
            class="btn btn-primary btn-sm btn-icon-only rounded-circle"
            type="button"
            @click="emits('actionRequested')"
            title="Add new"
          >
            <span class="btn-inner--icon">
              <svg
                height="18px"
                viewBox="0 0 16 16"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                style="margin: -3px"
              >
                <path
                  fill-rule="evenodd"
                  d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"
                ></path>
                <path
                  fill-rule="evenodd"
                  d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"
                ></path>
              </svg>
            </span>
          </button>
        </span>
      </span>
    </div>
    <div class="ant-transfer-list-body ant-transfer-list-body-with-search">
      <div class="ant-transfer-list-body-search-wrapper">
        <div>
          <input
            placeholder="Search here"
            type="search"
            @input="emits('searching', searchKey)"
            class="ant-input ant-transfer-list-search"
            v-model="searchKey"
          />
          <span class="ant-transfer-list-search-action">
            <i class="anticon anticon-search">
              <svg
                viewBox="64 64 896 896"
                data-icon="search"
                width="1em"
                height="1em"
                fill="currentColor"
                aria-hidden="true"
                focusable="false"
                class
              >
                <path
                  d="M909.6 854.5L649.9 594.8C690.2 542.7 712 479 712 412c0-80.2-31.3-155.4-87.9-212.1-56.6-56.7-132-87.9-212.1-87.9s-155.5 31.3-212.1 87.9C143.2 256.5 112 331.8 112 412c0 80.1 31.3 155.5 87.9 212.1C256.5 680.8 331.8 712 412 712c67 0 130.6-21.8 182.7-62l259.7 259.6a8.2 8.2 0 0 0 11.6 0l43.6-43.5a8.2 8.2 0 0 0 0-11.6zM570.4 570.4C528 612.7 471.8 636 412 636s-116-23.3-158.4-65.6C211.3 528 188 471.8 188 412s23.3-116.1 65.6-158.4C296 211.3 352.2 188 412 188s116.1 23.2 158.4 65.6S636 352.2 636 412s-23.3 116.1-65.6 158.4z"
                />
              </svg>
            </i>
          </span>
        </div>
      </div>
      <div class="ant-transfer-list-content mt-2">
        <slot name="body"></slot>
      </div>
    </div>
  </div>
</template>
