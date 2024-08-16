<template>
  <div class="text-center">
    <div class="error-code page-title mb-3">404</div>
    <h3 class="mb-4">
      <div class="badge bg-info">Page not found !</div>
    </h3>
    <p class="lead">
      Sorry, but the page you are looking for has not been found on our server.
    </p>
  </div>

  <div class="d-flex justify-content-center gap-3 mt-4">
    <button type="button" class="btn btn-light">Go back</button>
    <router-link :to="{ name: '' }" class="btn btn-primary"
      >Return Home</router-link
    >
  </div>
</template>
<script>
export default {
  name: "404",
};
</script>
