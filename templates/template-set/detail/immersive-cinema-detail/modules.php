<?php
/**
 * Immersive Cinema property modules.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/immersive-cinema-detail/modules.php
 *
 * Available variables: $property, $template, $facts, $rooms, $material, $features, $description, $overview, $location_label, $address, $documents, $office, $has_floorplan, $post_id, $button, $phone, $email, $agent, $agent_role, $agent_initials, $portrait.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<main class="ph-template-modules ph-template-modules-cinema" aria-label="<?php esc_attr_e( 'Property information', 'propertyhive' ); ?>">
	<?php if ( ! empty( $features ) ) : ?>
		<section>
			<h2><?php esc_html_e( 'Key features', 'propertyhive' ); ?></h2>
			<ul class="ph-template-module-features">
				<?php foreach ( $features as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php if ( $overview ) : ?>
		<section>
			<h2><?php esc_html_e( 'Overview', 'propertyhive' ); ?></h2>
			<p><?php echo esc_html( $overview ); ?></p>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $rooms ) || $description ) : ?>
		<section class="ph-template-module-rooms">
			<h2><?php esc_html_e( 'Room by room', 'propertyhive' ); ?></h2>
			<?php if ( $rooms ) : ?>
				<?php foreach ( $rooms as $room ) : ?>
					<p class="room">
						<?php if ( $room['name'] ) : ?><strong class="name"><?php echo esc_html( $room['name'] ); ?></strong><?php endif; ?>
						<?php if ( $room['dimensions'] ) : ?><span class="dimension"><?php echo esc_html( $room['dimensions'] ); ?></span><?php endif; ?>
						<?php if ( $room['description'] ) : ?><span class="description"><?php echo esc_html( $room['description'] ); ?></span><?php endif; ?>
					</p>
				<?php endforeach; ?>
			<?php else : ?>
				<?php echo wp_kses_post( $description ); ?>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php if ( $facts ) : ?>
		<section>
			<h2><?php esc_html_e( 'The details', 'propertyhive' ); ?></h2>
			<dl class="ph-template-dark-facts">
				<?php foreach ( $facts as $fact ) : ?>
					<div>
						<dt><?php echo esc_html( $fact['label'] ); ?></dt>
						<dd><?php echo esc_html( $fact['value'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>

	<?php if ( $material ) : ?>
		<section>
			<h2><?php esc_html_e( 'Material information', 'propertyhive' ); ?></h2>
			<dl class="ph-template-material-grid">
				<?php foreach ( $material as $item ) : ?>
					<div>
						<dt><?php echo esc_html( $item['label'] ); ?></dt>
						<dd><?php echo esc_html( $item['value'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>

	<?php if ( $documents ) : ?>
		<section>
			<h2><?php esc_html_e( 'Media & documents', 'propertyhive' ); ?></h2>
			<div class="ph-template-doc-row">
				<?php foreach ( $documents as $document ) : ?>
					<?php
					$doc_type  = ! empty( $document['type'] ) ? sanitize_html_class( $document['type'] ) : '';
					$doc_class = 'ph-template-doc-pill' . ( $doc_type ? ' ph-template-doc-pill-' . $doc_type : '' );
					?>
					<?php if ( ! empty( $document['url'] ) ) : ?>
						<a class="<?php echo esc_attr( $doc_class ); ?>" href="<?php echo esc_url( $document['url'] ); ?>"><?php echo esc_html( $document['label'] ); ?></a>
					<?php else : ?>
						<span class="<?php echo esc_attr( $doc_class ); ?>"><?php echo esc_html( $document['label'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $location_label || $address ) : ?>
		<section>
			<h2><?php esc_html_e( 'Location', 'propertyhive' ); ?></h2>
			<div class="ph-template-module-map-surface">
				<span class="ph-template-map-pin"></span>
				<span class="ph-template-map-label"><?php echo esc_html( $location_label ? $location_label : $address ); ?></span>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $agent || $portrait || $office || $email || $phone ) : ?>
		<section class="ph-template-cinema-agent-section">
			<div class="ph-template-cinema-agent-panel">
				<div class="ph-template-cinema-agent-row">
					<?php if ( $portrait ) : ?>
						<span class="ph-template-cinema-agent-avatar ph-template-cinema-agent-avatar-image"><?php echo wp_kses_post( $portrait ); ?></span>
					<?php elseif ( $agent || $agent_initials ) : ?>
						<span class="ph-template-cinema-agent-avatar" aria-hidden="true"><?php echo esc_html( $agent_initials ? $agent_initials : '?' ); ?></span>
					<?php endif; ?>
					<div class="ph-template-cinema-agent-meta">
						<?php if ( $agent || $office ) : ?>
							<div class="ph-template-cinema-agent-name"><?php echo esc_html( $agent ? $agent : $office ); ?></div>
						<?php endif; ?>
						<?php
						$role_bits = array_filter(
							array(
								$agent_role,
								$office && $agent ? $office : '',
								$phone,
							)
						);
						if ( $role_bits ) :
							?>
							<div class="ph-template-cinema-agent-role"><?php echo esc_html( implode( ' · ', $role_bits ) ); ?></div>
						<?php endif; ?>
					</div>
				</div>
				<div class="ph-template-cinema-agent-actions">
					<?php if ( $email ) : ?>
						<a class="ph-template-button ph-template-button-secondary" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'Email', 'propertyhive' ); ?></a>
					<?php endif; ?>
					<a class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" href="javascript:;"><?php echo esc_html( $button ? $button : __( 'Request viewing', 'propertyhive' ) ); ?></a>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
