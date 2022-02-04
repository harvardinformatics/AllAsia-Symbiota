<!-- Tippy.JS Tooltips Styles -->
<link
  rel="stylesheet"
  href="https://unpkg.com/tippy.js@6/animations/scale.css"
  type="text/css"
/>
<link
  rel="stylesheet"
  href="https://unpkg.com/tippy.js@6/animations/scale-subtle.css"
  type="text/css"
/>
<link
  rel="stylesheet"
  href="https://unpkg.com/tippy.js@6/themes/light.css"
  type="text/css"
/>
<!-- Tippy.JS Tooltips JS -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<!-- Symbiota Tooltips JS -->
<script src="<?php echo $CLIENT_ROOT; ?>/js/symb/symbiota.tooltips.js" defer></script>
<script type="text/javascript">
  /**
   * Checks if there are custom tooltips defined in the custom-tooltips.json file.
   * If not, then tooltips are fetched from API.
   */
  document.addEventListener("DOMContentLoaded", async function(){
    console.log('DOMContentLoaded');
    const relFilePath = <?php echo (json_encode($relFilePath)); ?>;
    const langTag = <?php echo (json_encode($LANG_TAG)); ?>;
    const customTooltips = await getTooltipFromFile("<?php echo $CLIENT_ROOT ?>/config/custom-tooltips.json");
    const pageTooltip = (customTooltips.hasOwnProperty(relFilePath)) ? customTooltips[relFilePath] : await getTooltip(relFilePath, langTag);
    const pageTitle = document.querySelector('#innertext h1');
    addTooltip(pageTitle, pageTooltip, langTag);
    const terms = document.querySelectorAll('.term');
    for (const term of terms) {
      const tooltipObj = (customTooltips.hasOwnProperty(`/terms/${term.dataset.term}`)) ? customTooltips[`/terms/${term.dataset.term}`] : await getTooltip(`/terms/${term.dataset.term}`, langTag);
      addTooltip(term, tooltipObj, langTag);
    }
  })
</script>
<style>
  .page-title {
    width: fit-content;
  }
  .page-title.has-tooltip, .term.has-tooltip {
    display: inline-block;
    cursor: pointer;
  }

  .page-title.has-tooltip:hover, .term.has-tooltip:hover {
    text-decoration: underline;
    text-decoration-style: dashed;
  }

  .page-title.has-tooltip:after, .term.has-tooltip:after {
    content: "";
    display: block;
    background: url(../../images/info2.png) no-repeat center;
    background-size: contain;
    width: 16px;
    height: 16px;
    float: right;
    padding-right: 6px;
  }

  .term.has-tooltip:after {
    margin-top: -6%;
  }

  .tooltip-link:before {
    content: "";
    display: block;
    background: url(../../images/link2.png) no-repeat center;
    background-size: contain;
    width: 20px;
    height: 18px;
    float: left;
    padding-right: 6px;
  }
</style>