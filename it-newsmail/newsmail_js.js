/* NewsMail JS */

function itnm_allNews_modified(){
	if($('#allNews-chkbx').prop("checked")){
			$('.itnm-cat-chkbx').prop('checked', true);
		} else {
			$('.itnm-cat-chkbx').prop('checked', false);
		}
}

function itnm_category_modified(){
	var allChecked = true;
	$('.itnm-cat-chkbx').each(function() {
		if(!$(this).prop('checked')){
			allChecked = false;
		}
	})

	$('#allNews-chkbx').prop('checked', allChecked);
}



$('#allNews-chkbx').on("click", function() {
	itnm_allNews_modified();
});

$('.itnm-cat-chkbx').on("click", function() {
	itnm_category_modified();
});
