<div class="wrap">
	<div id="icon-options-drip" class="icon32"><br></div>
	<h2>Drip Settings 
		<a href="http://www.getdrip.com/tour?utm_source=wp-plugin&utm_medium=link&utm_campaign=dashboard-header" class="drip-visit" target="_blank">Take a Tour</a>
		<a href="http://www.getdrip.com/contact?utm_source=wp-plugin&utm_medium=link&utm_campaign=dashboard-header" class="drip-visit" target="_blank">Support</a>
	</h2>
	<?php if ( ! $this->account_id() ) { ?>
		<div class="drip-settings-banner drip-clearfix">
			<h3>Create a Drip Account 
				<span class="drip-popdown">
					<a href="#">Already have an account?</a>
					<div class="message">Enter your Drip account ID in the form below to install your tracking script.</div>
				</span>
			</h3>
			<p><a href="http://www.getdrip.com/?utm_source=wp-plugin&utm_medium=link&utm_campaign=dashboard1" target="_blank">Drip</a> uses email and years of best practices to create a double-digit jump in your conversion rate.</p>
			<a href="http://www.getdrip.com/?utm_source=wp-plugin&utm_medium=link&utm_campaign=dashboard2" class="drip-button" target="_blank">Sign Up Here</a>
			<p class="drip-closer"><!-- [closer text tbd] --></p>
		</div>
	<?php } ?>
	<form name="drip-settings-form" method="post" action="options.php">
		<?php settings_fields( 'drip_options' ); ?>
		<?php do_settings_sections( 'drip' ); ?>
		<?php submit_button(); ?>
	</form>
</div>