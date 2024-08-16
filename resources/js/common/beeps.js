import { Howl, Howler } from "howler";
const path = process.env.BASE_URL;
export const placeOrderSound = new Howl({
  src: [`${path}audio/alert_alarm.mp3`],
});

export const cartItemAddedSound = new Howl({
  src: [`${path}audio/access.mp3`],
});
export const cartItemEditedSound = new Howl({
  src: [`${path}audio/click.mp3`],
});

// Change global volume.
Howler.volume(0.5);
