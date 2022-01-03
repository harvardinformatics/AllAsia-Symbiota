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
  document.addEventListener("DOMContentLoaded", async function(){
    const relFilePath = <?php echo (json_encode($relFilePath)); ?>;
    const langTag = <?php echo (json_encode($LANG_TAG)); ?>;
    const pageTooltip = await getTooltip(relFilePath);
    const pageTitle = document.querySelector('#innertext h1');
    addTooltip(pageTitle, pageTooltip, langTag);
    const terms = document.querySelectorAll('.term');
    for (const term of terms) {
      const tooltipObj = await getTooltip(`/terms/${term.dataset.term}`);
      addTooltip(term, tooltipObj, langTag);
    }
  })
</script>
<style>
  .page-title, .term {
    display: inline-block;
    cursor: pointer;
  }

  .page-title:hover, .term:hover {
    text-decoration: underline;
    text-decoration-style: dashed;
  }

  .page-title:after, .term:after {
    content: "";
    display: block;
    background: url(../../images/info2.png) no-repeat center;
    background-size: contain;
    width: 20px;
    height: 20px;
    float: right;
    padding-right: 6px;
  }

  .term:after {
    margin-top: -10%;
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