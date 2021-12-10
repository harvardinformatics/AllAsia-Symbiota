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
    const pageTooltipText = await getTooltip(relFilePath, langTag);
    const pageTitle = document.querySelector('#innertext h1');
    const terms = document.querySelectorAll('.term');
    for (const term of terms) {
      const termTooltipText = await getTooltip(`/terms/${term.dataset.term}`, langTag);
      tippy(term, {
        // trigger: 'click',
        theme: 'light',
        placement: 'bottom',
        allowHTML: true,
        interactive: true,
        interactiveBorder: 1,
        offset: [0, 10],
        onShow: function(instance) {
          instance.setContent(termTooltipText);
        }
      });
    }
    tippy('h1', {
      // trigger: 'click',
      // arrow: true,
      theme: 'light',
      placement: 'bottom',
      allowHTML: true,
      interactive: true,
      interactiveBorder: 1,
      offset: [20, 10],
      onShow: function(instance) {
        instance.setContent(pageTooltipText);
      }
    });
  })
</script>
<style>
  .page-title, .term {
    display: inline-block;
  }
</style>