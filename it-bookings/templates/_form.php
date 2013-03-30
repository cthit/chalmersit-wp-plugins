<?php if(is_user_logged_in()) : ?>

<?php global $current_user;?>

<form method="post" id="new-booking-form" name="new_booking" action="<?php the_permalink();?>">
	<input type="hidden" name="action" value="new_booking" />
	<?php wp_nonce_field( 'it-new-booking' ); ?>
	<?php do_action("it_bookings_feedback");?>

<p class="title-container">
	<label for="booking-title">Titel <span title="Detta fält är obligatoriskt" rel="tooltip" class="required">*</span></label>
	<input type="text" id="booking-title" name="title" placeholder="Ex. spelkväll med drawIT" <?php preserve_field($_POST['title']); ?> />
</p>

<?php $rooms = Booking::getLocations();?>

<?php if($rooms) : ?>
<label>Lokal</label>
<ul class="location-container horizontal-list">
	<?php $count = 0; foreach($rooms as $room) : ?>
	<li><label><input type="radio" name="location" value="<?php echo $room;?>" 
		<?php if($_POST['location'] == $room || $count==0) echo 'checked';?> /><?php echo $room;?></label>
	</li>

	<?php $count++; endforeach;?>
</ul>
<?php endif;?>

<ul class="booking-dates-container">
	<li>
		<label for="start-date">Startdatum</label>
		<input 	id="start-date" 
				type="date" 
				name="start_date" 
				maxlength="10" 
				class="booking-dates" 
				size="8" 
				<?php preserve_field($_POST['start_date'], date("Y-m-d"));?> />
		
		<select name="start_time">
			<?php for($i = 0; $i <= 24; $i++) :
				$hour = ($i < 10 ? "0".$i : $i) . ":00"; ?>

				<option value="<?php echo $hour;?>" <?php selected($_POST['start_time'], $hour);?> /><?php echo $hour;?></option>
			<?php endfor; ?>
		</select>
	</li>
	<li>
		<label for="end-date">Slutdatum</label>
		<input 	id="end-date" 
				type="date" 
				name="end_date" 
				class="booking-dates" 
				maxlength="10" 
				size="8" 
				<?php preserve_field($_POST['end_date'], date("Y-m-d"));?> />

		<select name="end_time">
			<?php for($i = 0; $i <= 24; $i++) :
				$hour = ($i < 10 ? "0".$i : $i) . ":00"; ?>

				<option value="<?php echo $hour;?>" <?php selected($_POST['end_time'], $hour);?> /><?php echo $hour;?></option>
			<?php endfor; ?>
		</select>
	</li>
</ul>

<p class="booking-repeat-container hide">
	<label><input type="checkbox" id="booking-repeat-check" name="booking_repeat" value="yes" <?php checked($_POST['booking_repeat'], 1); ?> /> Upprepa bokning veckovis</label>
</p>

<div class="booking-repeat-info hide">
	<p>
		<select name="booking_weekday" id="booking-weekday">
			<?php $days = array("Måndag", "Tisdag", "Onsdag", "Torsdag", "Fredag", "Lördag", "Söndag");
					$count = 1;

			foreach($days as $day) : ?>
			<option value="<?php echo $count;?>" <?php selected($_POST['booking_weekday'], $count); ?>><?php echo $day;?></option>
			<?php $count++; endforeach;?>
		</select>

		alalalla adaw awd 
	</p>
</div>

<p>
	<label for="booking-description">Beskrivning</label>
	<textarea id="booking-description" 
		name="description" 
		placeholder="Kort beskrivning av bokningen"><?php preserve_field($_POST['description'], "", false); ?></textarea>
</p>

<ul class="phone-and-groups-container">

	<li>
		<label for="booking-phone">Telefonnummer <span class="required">*</span></label>
		<input id="booking-phone" type="text" name="booking_phone" <?php preserve_field($_POST['booking_phone']);?> />
	</li>



<?php $groups = getGroupsForUser($current_user->ID);
		if($groups) : ?>
	<li>
		<label for="user-groups">Boka för</label>
		<select id="user-groups" name="user_groups">
		<?php foreach($groups as $g) : ?>
			<option value="<?php echo $g->group_id;?>"><?php echo $g->group_name;?></option>
		<?php endforeach;?>
		</select>
	</li>
<?php endif;?>
</ul>
<p class="party-container">
	<label title="Såklart du vill">
		<input 
			type="checkbox" 
			name="booking_is_party" 
			id="is-party-booking" 
			value="yes" <?php checked($_POST['booking_is_party'], 1);?> />

		Jag vill även festanmäla
	</label>
</p>

<div class="party-info-container">
	<ul>
		<li>
			<label for="party-responsible-name">Festansvarig <span class="required">*</span></label>
			<input name="party_responsible_name" type="text" <?php preserve_field($_POST['party_responsible_name']);?> />
		</li>
		<li>
			<label for="party-responsible-phone">Telefonnummer till festansvarig <span class="required">*</span></label>
			<input name="party_responsible_phone" type="text" <?php preserve_field($_POST['party_responsible_phone']);?> />
		</li>
	</ul>

	<p>
		<label><input type="checkbox" value="yes" name="party_has_warrant" <?php checked($_POST['party_has_warrant'], 1);?> />
			Alkoholtillstånd</label>
	</p>
</div>

<p class="submit-container">
	<input type="submit" value="Skicka bokning" />
</p>
</form>

<?php else : ?>

<p class="no-content">Du måste vara inloggad för att använda bokningsfunktionen.</p>

<?php endif;?>