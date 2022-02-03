/**
 * Fetches tooltip and documentation from external API
 * Symbiota Tooltips (https://github.com/BioKIC/symbiota-tooltips)
 * @dependencies Uses Tippy.js to bind tooltip to element
 * @author		Laura Rocha Prado (lauraprado[at]asu.edu)
 * @version		1.1
 * @date		2022-01
 */

/**
 * Fetches tooltip data from local JSON file
 * @param {String} path Path where tooltip data is stored
 * @returns Object with tooltip data
 */
async function getTooltipFromFile(path) {
  const response = await fetch(path);
  const data = await response.json();
  return data;
}

/**
 * Gets tooltip from external API
 * @param {string} path Path used to fetch tooltip and documentation in API call
 * @returns {Object} API response
 */
async function getTooltip(path) {
  const baseUrl = 'https://biokic.github.io/symbiota-tooltips/api/v1_0';
  const apiUrl = baseUrl + path + '.json';
  let tooltip = {};
  const res = await fetch(apiUrl);
  if (res.status === 404) {
    tooltip = false;
  } else {
    const data = await res.json();
    tooltip = data.data;
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
  // if tooltip object is not empty, add tooltip
  if (tooltip) {
    element.classList.add('has-tooltip');
    // Tooltip template
    const tooltipDiv = document.createElement('div');
    if (tooltip.tooltip[langTag] !== '') {
      ttText = document.createElement('p');
      ttText.innerText = tooltip.tooltip[langTag];
      tooltipDiv.appendChild(ttText);
    } else {
      element.classList.remove('has-tooltip');
    }
    // If there are links in tooltip, add them to the tooltip
    if (tooltip.resources !== undefined) {
      ttLinks = document.createElement('p');
      let links = tooltip.resources;
      for (let link in links) {
        if (links[link].href !== '') {
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
}
