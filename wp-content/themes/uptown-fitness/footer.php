<footer class="site-footer">
	<div class="footer-marquee" aria-hidden="true">
		<div>MOVE. GROW. REPEAT. <span>UPTOWN FITNESS</span> MOVE. GROW. REPEAT. <span>UPTOWN FITNESS</span></div>
	</div>
	<div class="footer-shell">
		<div class="footer-lead">
			<a class="brand brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="brand-mark">U</span>
				<span class="brand-copy">UPTOWN <b>FITNESS</b></span>
			</a>
			<h2>DEIN NÄCHSTER<br>SCHRITT STARTET HIER.</h2>
			<a class="button button--light" href="<?php echo esc_url( home_url( '/probetraining/' ) ); ?>">Probetraining sichern <span>↗</span></a>
		</div>

		<div class="footer-grid">
			<div>
				<h3>Entdecken</h3>
				<a href="<?php echo esc_url( home_url( '/training/' ) ); ?>">Training</a>
				<a href="<?php echo esc_url( home_url( '/kurse/' ) ); ?>">Kurse</a>
				<a href="<?php echo esc_url( home_url( '/coaches/' ) ); ?>">Coaches</a>
				<a href="<?php echo esc_url( home_url( '/preise/' ) ); ?>">Preise</a>
			</div>
			<div>
				<h3>Uptown</h3>
				<a href="<?php echo esc_url( home_url( '/ueber-uns/' ) ); ?>">Über uns</a>
				<a href="<?php echo esc_url( home_url( '/standorte/' ) ); ?>">Standorte</a>
				<a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">Community</a>
				<a href="<?php echo esc_url( home_url( '/magazin/' ) ); ?>">Magazin</a>
			</div>
			<div>
				<h3>Kontakt</h3>
				<a href="mailto:hallo@uptown-fitness.de">hallo@uptown-fitness.de</a>
				<a href="tel:+493055501270">+49 30 555 012 70</a>
				<a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Support & Kontakt</a>
				<span>24/7 für Mitglieder geöffnet</span>
			</div>
			<div>
				<h3>Updates</h3>
				<p>Trainingstipps, Club-News und neue Kurse direkt in dein Postfach.</p>
				<form class="footer-form" action="#" method="post">
					<label class="screen-reader-text" for="footer-email">E-Mail-Adresse</label>
					<input id="footer-email" type="email" placeholder="Deine E-Mail" required>
					<button type="submit" aria-label="Newsletter abonnieren">→</button>
				</form>
			</div>
		</div>

		<div class="footer-bottom">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Uptown Fitness</span>
			<div>
				<a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a>
				<a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a>
				<a href="<?php echo esc_url( home_url( '/agb/' ) ); ?>">AGB</a>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
