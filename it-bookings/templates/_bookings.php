<ul class="bookingtabs">
	<li><a href="#tab-createbooking">Skapa bokning</a></li>
	<li><a href="#tab-listbookings">Andra bokningar</a></li>
</ul>

<section class="tab-container">
	<div id="tab-createbooking">
		<?php include IT_BOOKING_PATH."/templates/_form.php";?>
	</div>
	<div id="tab-listbookings">
		<?php include IT_BOOKING_PATH."/templates/_list.php";?>
	</div>
</section>
