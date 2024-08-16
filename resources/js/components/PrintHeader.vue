<script>
export default {
  name: "PrintHeader",
};
</script>
<template>
  <div class="company-details">
    <div class="d-flex align-items-center justify-content-between">
      <div class="site-log">
        <img
          :src="getSiteLogo()"
          style="width: 150px"
          :alt="appSettings?.site_name"
        />
      </div>
      <div class="site-details">
        <div class="d-block">
          <h6>TIN number:{{ appSettings?.app_tin }}</h6>
          <h6>Tel:{{ appSettings?.app_phone }}</h6>
          <h6>Email:{{ appSettings?.app_email }}</h6>
          <p class="fw-bold">Address: {{ appSettings?.site_address }}</p>
        </div>
      </div>
    </div>
    <slot name="title"></slot>
    <div class="line"></div>
  </div>
</template>
