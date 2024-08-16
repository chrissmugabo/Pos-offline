<script setup></script>
<template>
  <div class="css-alerter">
    <div class="css-alerter-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
        <path
          d="M10.6364 9.16097L10.6366 9.1687L10.6372 9.17642L10.906 13.0932L10.9379 13.559H11.1278C11.0277 13.6248 10.9336 13.7031 10.8461 13.7939C10.5306 14.1218 10.38 14.5336 10.38 14.9918C10.38 15.4453 10.5331 15.8516 10.8461 16.1769C11.1615 16.5046 11.5641 16.6694 12.0192 16.6694C12.4743 16.6694 12.877 16.5046 13.1923 16.1769C13.5053 15.8516 13.6584 15.4453 13.6584 14.9918C13.6584 14.5336 13.5078 14.1218 13.1923 13.7939C13.1049 13.7031 13.0107 13.6248 12.9106 13.559H13.1005L13.1324 13.0932L13.4012 9.17642L13.4018 9.1687L13.4021 9.16097L13.4661 7.45863L13.4856 6.93984H12.9664H11.072H10.5529L10.5724 7.45863L10.6364 9.16097ZM12 4.5C16.1424 4.5 19.5 7.8576 19.5 12C19.5 16.1424 16.1424 19.5 12 19.5C7.8576 19.5 4.5 16.1424 4.5 12C4.5 7.8576 7.8576 4.5 12 4.5Z"
          fill="#222325"
          stroke="#222325"
        ></path>
      </svg>
    </div>
    <p class="css-alerter-desc" style="color: rgb(34, 35, 37)">
      Message Description Here
    </p>
    <a class="css-alerter-link">Action</a>
    <div class="css-alerter-close" style="position: relative; margin-left: 4px">
      <svg width="9" height="9" viewBox="0 0 15 15" version="1.1">
        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
          <path
            d="M5.38199729,7.5 L0.442285909,12.4397114 C-0.149212101,13.0312094 -0.144879314,13.9751387 0.439990983,14.560009 C1.02893889,15.1489569 1.97415086,15.1438518 2.56028861,14.5577141 L7.5,9.61800271 L12.4397114,14.5577141 C13.0312094,15.1492121 13.9751387,15.1448793 14.560009,14.560009 C15.1489569,13.9710611 15.1438518,13.0258491 14.5577141,12.4397114 L9.61800271,7.5 L14.5577141,2.56028861 C15.1492121,1.9687906 15.1448793,1.02486128 14.560009,0.439990983 C13.9710611,-0.148956922 13.0258491,-0.143851841 12.4397114,0.442285909 L7.5,5.38199729 L2.56028861,0.442285909 C1.9687906,-0.149212101 1.02486128,-0.144879314 0.439990983,0.439990983 C-0.148956922,1.02893889 -0.143851841,1.97415086 0.442285909,2.56028861 L5.38199729,7.5 Z"
            fill="#222325"
          ></path>
        </g>
      </svg>
    </div>
  </div>
</template>
<style scoped>
.css-alerter-close {
  cursor: pointer;
  display: flex;
  height: 20px;
  width: 20px;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-pack: center;
  justify-content: center;
}
.css-alerter {
  box-sizing: border-box;
  display: inline-flex;
  height: 48px;
  padding: 12px;
  min-width: 100%;
  width: 100%;
  pointer-events: all;
  background: rgb(255, 179, 62);
  -webkit-box-align: center;
  align-items: center;
  z-index: 99;
  position: relative;
}
.css-alerter-icon {
  display: flex;
  height: 32px;
  position: relative;
  width: 32px;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-pack: center;
  justify-content: center;
}
.css-alerter-desc {
  margin: 0px;
  font-size: 16px;
  font-weight: 400;
  letter-spacing: 0px;
  line-height: 24px;
  padding-left: 8px;
  padding-right: 20px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-grow: 1;
}
@media print {
  .css-alerter-desc {
    font-size: 12px;
    line-height: 16px;
  }
}
.css-alerter-link {
  color: rgb(34, 35, 37);
  cursor: pointer;
  font-size: 16px;
  letter-spacing: 0.2px;
  line-height: 24px;
  text-decoration: underline;
  font-weight: 600;
  white-space: nowrap;
}
.css-alerter-link:hover {
  color: rgb(34, 35, 37);
  text-decoration: none;
}
.css-alerter ~ #wrapper aside#aside-main {
  padding-top: 48px;
}
</style>
