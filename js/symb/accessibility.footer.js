document.addEventListener('DOMContentLoaded', ()=>{
	document.getElementById('accessibility-button').disabled=false;
	updateButtonTextBasedOnEnabledStylesheet('<?php echo $LANG['TOGGLE_508_OFF'] ?>', '<?php echo $LANG['TOGGLE_508_ON'] ?>');
});

const openDialogButton = document.getElementById('accessibility-options-button');
const accessibilityDialog = document.getElementById('accessibility-modal');

openDialogButton.addEventListener('click', function() {
	accessibilityDialog.showModal();
});
