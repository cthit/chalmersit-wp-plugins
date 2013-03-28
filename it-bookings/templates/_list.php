<?php 
	global $wpdb;
	$bookings = get_bookings(true);

	#TODO: Don't do separate SQL-queries in the foreach loop - do a JOIN in `get_bookings()` instead
?>

<?php if($bookings) : ?>
<ol class="booking-list">

	<?php foreach($bookings as $b) : ?>

	<?php
		if(!empty($b->booking_group))
			$group = $wpdb->get_var("SELECT name FROM it_groups_group WHERE group_id = $b->booking_group");

		$user = get_user_by("id", $b->user_id);

		if(date("j F", strtotime($b->start_time)) == date("j F", strtotime($b->end_time)) ) {
			$date = date("j F", strtotime($b->start_time)) ." ";
			$date .= date("H:i", strtotime($b->start_time)) ." - ";
			$date .= date("H:i", strtotime($b->end_time));
		}
		else {
			$date = date("j F, H:i", strtotime($b->start_time)) . " - ";
			$date .= date("j F, H:i", strtotime($b->end_time));
		}
	?>

	<li>
		<h4><?php echo $b->title;?></h4>
		<time><?php echo $date;?></time>
		|
		<?php echo $b->location;?>

		<div class="booking-details hide">
			<dl>
				<dt>Skapad</dt>
				<dd><?php echo $b->created_at;?></dd>

				<dt>Bokad av</dt>
				<dd><?php echo $user->data->display_name;?></dd>

				<?php if($group) : ?>
				<dt>Förening</dt>
				<dd><?php echo $group;?></dd>
				<?php endif;?>
			</dl>

			<?php if($b->description) : ?>
			<p class="booking-description">
				<?php echo $b->description;?>
			</p>
			<?php endif;?>
		</div>
	</li>

	<?php endforeach;?>
</ol>
<?php endif;?>