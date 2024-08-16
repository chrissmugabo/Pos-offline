<template>
  <div class="d-flex align-items-center justify-content-between">
    <div class="p-2 w-100">
      <div class="ant-empty">
        <div class="ant-empty-image" style="height: 60px">
          <img alt="empty" :src="`${publicPath}img/original.png`" />
        </div>
        <div class="ant-empty-description">
          <slot name="description"></slot>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: "EmptyResults",
  props: ["description"],
};
</script>
<style scoped>
.ant-empty {
  margin: 0 8px;
  font-size: 14px;
  line-height: 1.5715;
  text-align: center;
}
.ant-empty-image {
  height: 100px;
  margin-bottom: 8px;
}
.ant-empty-image img {
  height: 100%;
}
.ant-empty-footer {
  margin-top: 16px;
}
</style>
