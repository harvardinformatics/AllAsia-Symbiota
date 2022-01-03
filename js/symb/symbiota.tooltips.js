/**
 * Fetches tooltip and documentation from external API
 * Symbiota Tooltips (https://github.com/BioKIC/symbiota-tooltips)
 * @dependencies Uses Tippy.js to bind tooltip to element
 * @author		Laura Rocha Prado (lauraprado@asu.edu)
 * @version		1.0
 * @date		2021-12-01
 */

/**
 *
 * @param {string} path Path used to fetch tooltip and documentation in API call
 * @returns API response
 */
async function getTooltip(path) {
  const baseUrl = 'https://biokic.github.io/symbiota-tooltips/api';
  const apiUrl = baseUrl + path + '.json';
  let tooltip = {};
  const res = await fetch(apiUrl);
  if (res.status === 404) {
    tooltipText = 'There is no help text for this term yet.';
  } else {
    const data = await res.json();
    tooltip = data[0];
  }
  return tooltip;
}

/**
 * Adds interactive tooltip to element, using Tippy.js
 * @param {Element} element
 * @param {Object} tooltip
 * @param {String} langTag
 */
function addTooltip(element, tooltip, langTag) {
  // Tooltip template
  const tooltipDiv = document.createElement('div');
  ttText = document.createElement('p');
  ttText.innerText = tooltip.tooltip[langTag];
  tooltipDiv.appendChild(ttText);
  // If there are links in tooltip, add them to the tooltip
  if (tooltip.resources !== undefined) {
    console.log(tooltip.resources);
    console.dir(Object.keys(tooltip.resources).length);
    ttLinks = document.createElement('p');
    let links = tooltip.resources;
    for (let link in links) {
      console.log(link);
      let linkA = document.createElement('a');
      linkA.className = 'tooltip-link';
      linkA.href = links[link].href;
      linkA.innerText = links[link].meta?.name;
      let linkSource = document.createElement('span');
      linkSource.innerText = ' (' + links[link].meta?.source + ')';
      let linkBr = document.createElement('br');
      ttLinks.appendChild(linkA);
      ttLinks.appendChild(linkSource);
      ttLinks.appendChild(linkBr);
    }
    tooltipDiv.appendChild(ttLinks);
  }

  tippy(element, {
    trigger: 'click',
    theme: 'light',
    placement: 'bottom',
    allowHTML: true,
    interactive: true,
    interactiveBorder: 1,
    offset: [20, 10],
    onShow: function (instance) {
      instance.setContent(tooltipDiv);
    },
  });
}
