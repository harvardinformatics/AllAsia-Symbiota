/***
 * NEON Mammals Wet Labels Custom Styles
 * Author: Ed Gilbert
 * Version: Aug 2023
 *
 * Features:
 * - Replaces standard Symbiota barcode with custom (using NEON tag (barcode) instead of catalogNumber)
 * - Barcodes courtesy of barcode.tec-it.com
 * - Removes orcid from recordedby
 * - Removes NEON UUID
 * - Removes NEON Hash
 */

let labels = document.querySelectorAll('.label');
labels.forEach((label) => {
  let catNums = label.querySelector('.other-catalog-numbers');
  let cArr = catNums.innerText.split(';');
  let newCatNum = '';
  cArr.forEach((catNum) => {
    // skip if it's a NEON UUID
    if (catNum.includes('sampleUUID') || catNum.includes('Hash')) {
      return;
    } else {
      newCatNum += `<span class="block">${catNum.trim()}</span>`;
      let bcSrc = label.querySelector('.cn-barcode img');
      if (bcSrc) {
        if (catNum.includes('barcode')) {
          let barcode = catNum.match(/(?<=barcode\): ).*/)[0].trim();
          bcSrc.src =
            'https://barcode.tec-it.com/barcode.ashx?data=' + barcode + '&code=Code128';
          return true;
        }
      }
    }
  });
  catNums.innerHTML = newCatNum;
  catNums.classList.add('mt-2');
});
