<script setup></script>
<template>
  <div
    role="progressbar"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-hidden="true"
    mode="indeterminate"
    class="mat-mdc-progress-bar mdc-linear-progress header-loader mat-primary mdc-linear-progress--animation-ready mdc-linear-progress--indeterminate gmat-mdc-progress-bar ng-star-inserted"
  >
    <div aria-hidden="true" class="mdc-linear-progress__buffer">
      <div
        class="mdc-linear-progress__buffer-bar"
        style="flex-basis: 100%"
      ></div>
      <div class="mdc-linear-progress__buffer-dots"></div>
    </div>
    <div
      aria-hidden="true"
      class="mdc-linear-progress__bar mdc-linear-progress__primary-bar"
      style="transform: scaleX(1); color: #4285f4; background: #4285f4"
    >
      <span class="mdc-linear-progress__bar-inner"></span>
    </div>
    <div
      aria-hidden="true"
      class="mdc-linear-progress__bar mdc-linear-progress__secondary-bar"
    >
      <span class="mdc-linear-progress__bar-inner"></span>
    </div>
  </div>
</template>
<style scoped>
.mat-mdc-progress-bar {
  --mdc-linear-progress-active-indicator-color: var(--primary);
  --mdc-linear-progress-track-color: rgba(26, 115, 232, 0.25);
}
.mat-mdc-progress-bar .mdc-linear-progress__buffer-dots {
  background-color: rgba(26, 115, 232, 0.25);
  background-color: var(
    --mdc-linear-progress-track-color,
    rgba(26, 115, 232, 0.25)
  );
}
@media (forced-colors: active) {
  .mat-mdc-progress-bar .mdc-linear-progress__buffer-dots {
    background-color: ButtonBorder;
  }
}
@media (-ms-high-contrast: active), (-ms-high-contrast: none) {
  .mat-mdc-progress-bar .mdc-linear-progress__buffer-dots {
    background-color: transparent;
    background-image: url("data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' enable-background='new 0 0 5 2' xml:space='preserve' viewBox='0 0 5 2' preserveAspectRatio='none slice'%3E%3Ccircle cx='1' cy='1' r='1' fill='rgba(26, 115, 232, 0.25)'/%3E%3C/svg%3E");
  }
}
.mat-mdc-progress-bar .mdc-linear-progress__buffer-bar {
  background-color: rgba(26, 115, 232, 0.25);
  background-color: var(
    --mdc-linear-progress-track-color,
    rgba(26, 115, 232, 0.25)
  );
}
.gmat-mdc-progress-bar.mat-primary {
  --mdc-linear-progress-active-indicator-color: var(--primary);
  --mdc-linear-progress-track-color: #e8f0fe;
}
.header-loader[_ngcontent-ng-c2659418406] {
  position: absolute;
  top: unset;
  z-index: 62;
}
.mdc-linear-progress {
  position: relative;
  width: 100%;
  transform: translateZ(0);
  outline: 1px solid rgba(0, 0, 0, 0);
  /* overflow-x: hidden; */
  transition: opacity 250ms 0ms cubic-bezier(0.4, 0, 0.6, 1);
}
@media screen and (forced-colors: active) {
  .mdc-linear-progress {
    outline-color: CanvasText;
  }
}
.mdc-linear-progress__bar {
  position: absolute;
  top: 0;
  bottom: 0;
  margin: auto 0;
  width: 100%;
  animation: none;
  transform-origin: top left;
  transition: transform 250ms 0ms cubic-bezier(0.4, 0, 0.6, 1);
}
.mdc-linear-progress__bar-inner {
  display: inline-block;
  position: absolute;
  width: 100%;
  animation: none;
  border-top-style: solid;
}
.mdc-linear-progress__buffer {
  display: flex;
  position: absolute;
  top: 0;
  bottom: 0;
  margin: auto 0;
  width: 100%;
  overflow: hidden;
}
.mdc-linear-progress__buffer-dots {
  background-repeat: repeat-x;
  flex: auto;
  transform: rotate(180deg);
  -webkit-mask-image: url("data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' enable-background='new 0 0 5 2' xml:space='preserve' viewBox='0 0 5 2' preserveAspectRatio='xMinYMin slice'%3E%3Ccircle cx='1' cy='1' r='1'/%3E%3C/svg%3E");
  mask-image: url("data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' enable-background='new 0 0 5 2' xml:space='preserve' viewBox='0 0 5 2' preserveAspectRatio='xMinYMin slice'%3E%3Ccircle cx='1' cy='1' r='1'/%3E%3C/svg%3E");
  animation: mdc-linear-progress-buffering 250ms infinite linear;
}
.mdc-linear-progress__buffer-bar {
  flex: 0 1 100%;
  transition: flex-basis 250ms 0ms cubic-bezier(0.4, 0, 0.6, 1);
}
.mdc-linear-progress__primary-bar {
  transform: scaleX(0);
}
.mdc-linear-progress__secondary-bar {
  display: none;
}
.mdc-linear-progress--indeterminate .mdc-linear-progress__bar {
  transition: none;
}
.mdc-linear-progress--indeterminate .mdc-linear-progress__primary-bar {
  left: -145.166611%;
}
.mdc-linear-progress--indeterminate .mdc-linear-progress__secondary-bar {
  left: -54.888891%;
  display: block;
}
.mdc-linear-progress--indeterminate.mdc-linear-progress--animation-ready
  .mdc-linear-progress__primary-bar {
  animation: mdc-linear-progress-primary-indeterminate-translate 2s infinite
    linear;
}
.mdc-linear-progress--indeterminate.mdc-linear-progress--animation-ready
  .mdc-linear-progress__primary-bar
  > .mdc-linear-progress__bar-inner {
  animation: mdc-linear-progress-primary-indeterminate-scale 2s infinite linear;
}
.mdc-linear-progress--indeterminate.mdc-linear-progress--animation-ready
  .mdc-linear-progress__secondary-bar {
  animation: mdc-linear-progress-secondary-indeterminate-translate 2s infinite
    linear;
}
.mdc-linear-progress--indeterminate.mdc-linear-progress--animation-ready
  .mdc-linear-progress__secondary-bar
  > .mdc-linear-progress__bar-inner {
  animation: mdc-linear-progress-secondary-indeterminate-scale 2s infinite
    linear;
}
.mdc-linear-progress__bar-inner {
  border-color: var(--mdc-linear-progress-active-indicator-color);
}
@media (forced-colors: active) {
  .mdc-linear-progress__buffer-dots {
    background-color: ButtonBorder;
  }
}
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
  .mdc-linear-progress__buffer-dots {
    background-color: rgba(0, 0, 0, 0);
    background-image: url("data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' enable-background='new 0 0 5 2' xml:space='preserve' viewBox='0 0 5 2' preserveAspectRatio='none slice'%3E%3Ccircle cx='1' cy='1' r='1' fill=''/%3E%3C/svg%3E");
  }
}
.mdc-linear-progress {
  height: max(
    var(--mdc-linear-progress-track-height),
    var(--mdc-linear-progress-active-indicator-height)
  );
}
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
  .mdc-linear-progress {
    height: 4px;
  }
}
.mdc-linear-progress__bar {
  height: var(--mdc-linear-progress-active-indicator-height);
}
.mdc-linear-progress__bar-inner {
  border-top-width: var(--mdc-linear-progress-active-indicator-height);
}
.mdc-linear-progress__buffer {
  height: var(--mdc-linear-progress-track-height);
}
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
  .mdc-linear-progress__buffer-dots {
    background-size: 10px var(--mdc-linear-progress-track-height);
  }
}
.mdc-linear-progress__buffer {
  border-radius: var(--mdc-linear-progress-track-shape);
}
.mat-mdc-progress-bar {
  display: block;
  --mdc-linear-progress-primary-half: 83.67142%;
  --mdc-linear-progress-primary-full: 200.611057%;
  --mdc-linear-progress-secondary-quarter: 37.651913%;
  --mdc-linear-progress-secondary-half: 84.386165%;
  --mdc-linear-progress-secondary-full: 160.277782%;
  --mdc-linear-progress-primary-half-neg: -83.67142%;
  --mdc-linear-progress-primary-full-neg: -200.611057%;
  --mdc-linear-progress-secondary-quarter-neg: -37.651913%;
  --mdc-linear-progress-secondary-half-neg: -84.386165%;
  --mdc-linear-progress-secondary-full-neg: -160.277782%;
}
/*! CSS Used keyframes */
@keyframes mdc-linear-progress-buffering {
  from {
    transform: rotate(180deg)
      translateX(calc(var(--mdc-linear-progress-track-height) * -2.5));
  }
}
@keyframes mdc-linear-progress-primary-indeterminate-translate {
  0% {
    transform: translateX(0);
  }
  20% {
    animation-timing-function: cubic-bezier(0.5, 0, 0.701732, 0.495819);
    transform: translateX(0);
  }
  59.15% {
    animation-timing-function: cubic-bezier(0.302435, 0.381352, 0.55, 0.956352);
    transform: translateX(var(--mdc-linear-progress-primary-half));
  }
  100% {
    transform: translateX(var(--mdc-linear-progress-primary-full));
  }
}
@keyframes mdc-linear-progress-primary-indeterminate-scale {
  0% {
    transform: scaleX(0.08);
  }
  36.65% {
    animation-timing-function: cubic-bezier(0.334731, 0.12482, 0.785844, 1);
    transform: scaleX(0.08);
  }
  69.15% {
    animation-timing-function: cubic-bezier(0.06, 0.11, 0.6, 1);
    transform: scaleX(0.661479);
  }
  100% {
    transform: scaleX(0.08);
  }
}
@keyframes mdc-linear-progress-secondary-indeterminate-translate {
  0% {
    animation-timing-function: cubic-bezier(0.15, 0, 0.515058, 0.409685);
    transform: translateX(0);
  }
  25% {
    animation-timing-function: cubic-bezier(0.31033, 0.284058, 0.8, 0.733712);
    transform: translateX(var(--mdc-linear-progress-secondary-quarter));
  }
  48.35% {
    animation-timing-function: cubic-bezier(0.4, 0.627035, 0.6, 0.902026);
    transform: translateX(var(--mdc-linear-progress-secondary-half));
  }
  100% {
    transform: translateX(var(--mdc-linear-progress-secondary-full));
  }
}
@keyframes mdc-linear-progress-secondary-indeterminate-scale {
  0% {
    animation-timing-function: cubic-bezier(
      0.205028,
      0.057051,
      0.57661,
      0.453971
    );
    transform: scaleX(0.08);
  }
  19.15% {
    animation-timing-function: cubic-bezier(
      0.152313,
      0.196432,
      0.648374,
      1.004315
    );
    transform: scaleX(0.457104);
  }
  44.15% {
    animation-timing-function: cubic-bezier(
      0.257759,
      -0.003163,
      0.211762,
      1.38179
    );
    transform: scaleX(0.72796);
  }
  100% {
    transform: scaleX(0.08);
  }
}
</style>
