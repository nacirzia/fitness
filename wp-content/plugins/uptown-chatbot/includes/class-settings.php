<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Chatbot_Settings {

	const OPTION = 'uptown_chatbot_settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function defaults() {
		return array(
			'enabled'     => '1',
			'api_key'     => '',
			'model'       => 'gemini-3.5-flash',
			'theme_color' => '#f53c0f',
			'bot_name'    => 'Uptown',
			'language'    => 'auto',
			'knowledge'   => self::default_knowledge(),
		);
	}

	public static function get() {
		return wp_parse_args( get_option( self::OPTION, array() ), self::defaults() );
	}

	public static function menu() {
		add_menu_page(
			__( 'Uptown Chatbot', 'uptown-chatbot' ),
			__( 'Chatbot', 'uptown-chatbot' ),
			'manage_options',
			'uptown-chatbot',
			array( __CLASS__, 'render' ),
			'dashicons-format-chat',
			58
		);
	}

	public static function register() {
		register_setting(
			'uptown_chatbot',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);
	}

	public static function sanitize( $input ) {
		$current  = self::get();
		$input    = is_array( $input ) ? $input : array();
		$api_key  = isset( $input['api_key'] ) ? trim( (string) $input['api_key'] ) : '';
		$models   = array( 'gemini-3.5-flash', 'gemini-3.1-flash-lite', 'gemini-flash-latest' );
		$langs    = array( 'auto', 'de', 'en' );

		return array(
			'enabled'     => ! empty( $input['enabled'] ) ? '1' : '0',
			'api_key'     => ( '' === $api_key || false !== strpos( $api_key, '•' ) ) ? $current['api_key'] : sanitize_text_field( $api_key ),
			'model'       => in_array( $input['model'] ?? '', $models, true ) ? $input['model'] : 'gemini-3.5-flash',
			'theme_color' => self::sanitize_hex( $input['theme_color'] ?? '#f53c0f' ),
			'bot_name'    => sanitize_text_field( $input['bot_name'] ?? 'Uptown' ),
			'language'    => in_array( $input['language'] ?? '', $langs, true ) ? $input['language'] : 'auto',
			'knowledge'   => sanitize_textarea_field( $input['knowledge'] ?? '' ),
		);
	}

	public static function sanitize_hex( $color ) {
		$color = sanitize_hex_color( $color );
		return $color ? $color : '#f53c0f';
	}

	public static function assets( $hook ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'toplevel_page_uptown-chatbot' !== $hook && 'uptown-chatbot' !== $page ) {
			return;
		}

		wp_enqueue_style(
			'uptown-chatbot-admin',
			UPTOWN_CHATBOT_URL . 'assets/css/admin.css',
			array(),
			UPTOWN_CHATBOT_VERSION
		);
		wp_enqueue_script(
			'uptown-chatbot-admin',
			UPTOWN_CHATBOT_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			UPTOWN_CHATBOT_VERSION,
			true
		);
		wp_localize_script(
			'uptown-chatbot-admin',
			'UptownChatbotAdmin',
			array(
				'restUrl' => esc_url_raw( rest_url( 'uptown-chatbot/v1/test' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$s        = self::get();
		$masked   = $s['api_key'] ? str_repeat( '•', 12 ) . substr( $s['api_key'], -4 ) : '';
		$presets  = array(
			'#f53c0f' => __( 'Uptown Orange', 'uptown-chatbot' ),
			'#2ecc71' => __( 'Tawk Green', 'uptown-chatbot' ),
			'#6d00ff' => __( 'Purple', 'uptown-chatbot' ),
			'#111111' => __( 'Black', 'uptown-chatbot' ),
			'#0ea5e9' => __( 'Sky', 'uptown-chatbot' ),
		);
		?>
		<div class="wrap uptown-chatbot-admin">
			<h1><?php esc_html_e( 'Uptown Chatbot', 'uptown-chatbot' ); ?></h1>
			<p class="description"><?php esc_html_e( 'A Tawk-style gym assistant that answers visitors in German or English.', 'uptown-chatbot' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'uptown_chatbot' ); ?>

				<div class="ucb-grid">
					<div class="ucb-card">
						<h2><?php esc_html_e( 'Status & API', 'uptown-chatbot' ); ?></h2>

						<label class="ucb-switch">
							<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[enabled]" value="1" <?php checked( $s['enabled'], '1' ); ?>>
							<span><?php esc_html_e( 'Turn chatbot on', 'uptown-chatbot' ); ?></span>
						</label>

						<p>
							<label for="ucb-api-key"><strong><?php esc_html_e( 'Gemini API key', 'uptown-chatbot' ); ?></strong></label>
							<input id="ucb-api-key" class="regular-text" type="password" name="<?php echo esc_attr( self::OPTION ); ?>[api_key]" value="<?php echo esc_attr( $masked ); ?>" autocomplete="off" placeholder="AIza...">
							<button type="button" class="button" id="ucb-toggle-key"><?php esc_html_e( 'Show', 'uptown-chatbot' ); ?></button>
						</p>
						<p class="description">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: Google AI Studio URL */
									__( 'Create a key in <a href="%s" target="_blank" rel="noopener">Google AI Studio</a>. The key is stored in WordPress and never sent to the browser.', 'uptown-chatbot' ),
									'https://aistudio.google.com/apikey'
								)
							);
							?>
						</p>

						<p>
							<label for="ucb-model"><strong><?php esc_html_e( 'Gemini model', 'uptown-chatbot' ); ?></strong></label><br>
							<select id="ucb-model" name="<?php echo esc_attr( self::OPTION ); ?>[model]">
								<option value="gemini-3.5-flash" <?php selected( $s['model'], 'gemini-3.5-flash' ); ?>>gemini-3.5-flash</option>
								<option value="gemini-3.1-flash-lite" <?php selected( $s['model'], 'gemini-3.1-flash-lite' ); ?>>gemini-3.1-flash-lite</option>
								<option value="gemini-flash-latest" <?php selected( $s['model'], 'gemini-flash-latest' ); ?>>gemini-flash-latest</option>
							</select>
						</p>

						<p>
							<button type="button" class="button button-secondary" id="ucb-test-key"><?php esc_html_e( 'Test Gemini connection', 'uptown-chatbot' ); ?></button>
							<span id="ucb-test-result" class="ucb-test-result" aria-live="polite"></span>
						</p>
					</div>

					<div class="ucb-card">
						<h2><?php esc_html_e( 'Appearance', 'uptown-chatbot' ); ?></h2>
						<p>
							<label for="ucb-bot-name"><strong><?php esc_html_e( 'Bot name', 'uptown-chatbot' ); ?></strong></label><br>
							<input id="ucb-bot-name" class="regular-text" type="text" name="<?php echo esc_attr( self::OPTION ); ?>[bot_name]" value="<?php echo esc_attr( $s['bot_name'] ); ?>">
						</p>
						<p>
							<label for="ucb-language"><strong><?php esc_html_e( 'Default language', 'uptown-chatbot' ); ?></strong></label><br>
							<select id="ucb-language" name="<?php echo esc_attr( self::OPTION ); ?>[language]">
								<option value="auto" <?php selected( $s['language'], 'auto' ); ?>><?php esc_html_e( 'Auto (visitor browser)', 'uptown-chatbot' ); ?></option>
								<option value="de" <?php selected( $s['language'], 'de' ); ?>><?php esc_html_e( 'German', 'uptown-chatbot' ); ?></option>
								<option value="en" <?php selected( $s['language'], 'en' ); ?>><?php esc_html_e( 'English', 'uptown-chatbot' ); ?></option>
							</select>
						</p>
						<p>
							<label for="ucb-theme-color"><strong><?php esc_html_e( 'Color theme', 'uptown-chatbot' ); ?></strong></label><br>
							<input id="ucb-theme-color" type="color" class="ucb-color" name="<?php echo esc_attr( self::OPTION ); ?>[theme_color]" value="<?php echo esc_attr( $s['theme_color'] ); ?>">
						</p>
						<div class="ucb-presets" role="group" aria-label="<?php esc_attr_e( 'Color presets', 'uptown-chatbot' ); ?>">
							<?php foreach ( $presets as $hex => $label ) : ?>
								<button type="button" class="ucb-preset" data-color="<?php echo esc_attr( $hex ); ?>" style="--ucb-preset:<?php echo esc_attr( $hex ); ?>" title="<?php echo esc_attr( $label ); ?>">
									<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
						<div class="ucb-preview" style="--ucb-theme:<?php echo esc_attr( $s['theme_color'] ); ?>">
							<div class="ucb-preview__bar"><?php echo esc_html( $s['bot_name'] ); ?></div>
							<div class="ucb-preview__bubble"><?php esc_html_e( 'Hi! How can I help you today?', 'uptown-chatbot' ); ?></div>
							<div class="ucb-preview__dot"></div>
						</div>
					</div>
				</div>

				<div class="ucb-card">
					<h2><?php esc_html_e( 'Gym knowledge', 'uptown-chatbot' ); ?></h2>
					<p class="description"><?php esc_html_e( 'The bot uses this text for hours, fees, and club facts. Update it whenever prices or opening times change.', 'uptown-chatbot' ); ?></p>
					<textarea name="<?php echo esc_attr( self::OPTION ); ?>[knowledge]" rows="16" class="large-text code"><?php echo esc_textarea( $s['knowledge'] ); ?></textarea>
				</div>

				<?php submit_button( __( 'Save settings', 'uptown-chatbot' ) ); ?>
			</form>
		</div>
		<?php
	}

	public static function default_knowledge() {
		return <<<TXT
Uptown Fitness is a smart 24/7 gym brand in Germany (Uptown Fitness GmbH, Alexanderstraße 18, 10178 Berlin).

HOURS
- Clubs are open 24/7 for active members via app check-in.
- Staffed support: Monday–Friday 08:00–20:00, Saturday 10:00–16:00.
- Contact: hallo@uptown-fitness.de / +49 30 555 012 70.

MEMBERSHIP PRICES (monthly, orientation – regional actions may differ)
- FLEX 39.90 €: monthly cancellation, home club, app, digital start plan.
- SMART 29.90 €: 12-month term, home club, app, check-up, start plan.
- COMPLETE 49.90 €: all clubs, regular classes, monthly coach check, extended app analytics.
- One-time start fee: 29 € (activation, club intro, first training plan).
- Free trial workout available before signing.
- Student, apprentice, and corporate rates on request.
- Regular memberships from age 16; under 18 need guardian consent.

CLUBS
- Berlin Mitte, Alexanderstraße 18: Strength, Cardio, Functional, 24/7.
- Hamburg Altona, Neue Große Bergstraße 44: Strength, Mobility, Classes, 24/7.
- Köln Ehrenfeld, Venloer Straße 312: Strength, Functional, Recovery, 24/7.
- Coming soon: Berlin Kreuzberg, Düsseldorf Bilk, Frankfurt Ostend.

TRAINING & CLASSES
- Zones: strength, functional, cardio, yoga/mobility, recovery.
- Classes include Uptown HIIT, Power Strength, Ride, Yoga, Mobility.
- Regular classes included in COMPLETE; other plans can add classes.
- Personal training sessions and packages available via the club team.

APP
- Mobile 24/7 access, live club occupancy, training plans, class booking, contract self-service.

LINKS
- Trial: /probetraining/
- Prices: /preise/
- Locations: /standorte/
- Contact: /kontakt/
TXT;
	}
}
