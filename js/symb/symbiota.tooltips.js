/**
 * Fetches tooltip and documentation from external API
 * Symbiota Tooltips (https://github.com/BioKIC/symbiota-tooltips)
 */
async function getTooltip(term, langTag) {
  // const apiUrl =
  //   'https://biokic.github.io/symbiota-tooltips/api' + term + '.json';
  const apiUrl =
    'https://laura.rochaprado.com/symbiota-tooltips/api' + term + '.json';
  // let tooltipText = '';
  // tooltipText = await (fetch(url));
  console.log(apiUrl);
  let tooltipText = '';
  const res = await fetch(apiUrl);
  if (res.status === 404) {
    console.log('The requested tooltip does not exist.');
    tooltipText = 'There is no help text for this term yet.';
  } else {
    const data = await res.json();
    tooltipText = data[0].tooltip[langTag];
  }
  return tooltipText;
}
