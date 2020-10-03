<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Github Sponsors implementation for WordPress themes & plugins.
 *
 * @package aristath/gh-sponsors-list
 */

namespace Aristath;

/**
 * Github Sponsors implementation for WordPress themes & plugins.
 */
class GHSponsors {

	/**
	 * An array of sponsors.
	 *
	 * @access protected
	 *
	 * @since 1.0
	 *
	 * @var array
	 */
	protected $sponsors = array();

	/**
	 * The GitHub-API token.
	 *
	 * @access protected
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Hooks prefix.
	 *
	 * This is used to prefix filters & actions.
	 *
	 * @access protected
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $gh_username;

	/**
	 * Should we add the styles?
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @var bool
	 */
	public $add_styles = true;

	/**
	 * Set the GitHub Username.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param string $username The username.
	 *
	 * @return void
	 */
	public function set_gh_username( $username ) {
		$this->gh_username = $username;
	}

	/**
	 * Get an array of sponsors.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_all_sponsors() {
		$sponsors      = array();
		$sponsor_names = array();

		// Remove duplicates if needed.
		foreach ( $this->sponsors as $sponsor ) {
			if ( ! in_array( $sponsor['name'], $sponsor_names, true ) ) {
				$sponsor_names[] = $sponsor['name'];
				$sponsors[]      = $sponsor;
			}
		}
		return $sponsors;
	}

	/**
	 * Add a sponsor.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $args The sponsor args [name, url, img, classes].
	 *
	 * @return void
	 */
	public function add_sponsor( $args ) {
		$this->sponsors[] = $args;
	}

	/**
	 * Add sponsors from the Github Sponsors API.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_sponsors_from_api() {

		// Get sponsors.
		$github_sponsors = $this->get_sponsors();

		// Loop sponsors and add them to the object's $sponsors property.
		foreach ( $github_sponsors as $gh_sponsor ) {
			$this->sponsors[] = array(
				'name'    => $gh_sponsor->node->sponsor->name,
				'url'     => $gh_sponsor->node->sponsor->websiteUrl || $gh_sponsor->node->sponsor->url,
				'img'     => $gh_sponsor->node->sponsor->avatarUrl,
				'classes' => 'round',
			);
		}
	}

	/**
	 * Add sponsors details.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function the_sponsors_details() {
		?>
		<div id="<?php echo esc_attr( $this->gh_username ); ?>-sponsors">
			<div id="<?php echo esc_attr( $this->gh_username ); ?>-sponsors-logos">
				<?php foreach ( $this->sponsors as $sponsor ) : ?>
					<a href="<?php echo esc_url( $sponsor['url'] ); ?>" target="_blank" rel="nofollow" class="<?php echo esc_attr( $sponsor['classes'] ); ?>">
						<img src="<?php echo esc_url( $sponsor['img'] ); ?>" alt="<?php echo esc_attr( $sponsor['name'] ); ?>">
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $this->add_styles ) : ?>
			<style>
				#<?php echo esc_attr( $this->gh_username ); ?>-sponsors { text-align: center; }
				#<?php echo esc_attr( $this->gh_username ); ?>-sponsors-logos { display: flex; }
				#<?php echo esc_attr( $this->gh_username ); ?>-sponsors-logos > a { padding: 0.25em; }
				#<?php echo esc_attr( $this->gh_username ); ?>-sponsors-logos > a img { width: auto; height: 2em; }
				#<?php echo esc_attr( $this->gh_username ); ?>-sponsors-logos > a.round img { border-radius: 50%; }
			</style>
		<?php endif; ?>
		<?php
	}

	/**
	 * Get sponsors.
	 *
	 * @access private
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	private function get_sponsors() {
		$transient_name = md5( "github sponsors {$this->gh_username}" );
		$sponsors       = get_transient( $transient_name );
		if ( ! $sponsors ) {
			$query    = 'query($cursor:String){user(login:"' . $this->gh_username . '"){sponsorshipsAsMaintainer(first:100 after:$cursor){pageInfo {startCursor endCursor hasNextPage } edges { node { sponsor { avatarUrl login name url websiteUrl }}}}}}';
			$response = wp_safe_remote_post(
				'https://api.github.com/graphql',
				array(
					'headers' => array(
						'Authorization' => 'bearer ' . $this->get_token(),
						'Content-type'  => 'application/json',
					),
					'body'    => wp_json_encode( array( 'query' => $query ) ),
					'timeout' => 20,
				)
			);

			$sponsors = array();

			if ( ! empty( wp_remote_retrieve_response_code( $response ) ) && ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $body->data ) && isset( $body->data->user ) && isset( $body->data->user->sponsorshipsAsMaintainer ) && isset( $body->data->user->sponsorshipsAsMaintainer->edges ) ) {
					$sponsors = $body->data->user->sponsorshipsAsMaintainer->edges;
				}
			}

			set_transient( $transient_name, $sponsors, DAY_IN_SECONDS );
		}
		return $sponsors;
	}

	/**
	 * Get the token.
	 *
	 * Returns a token which has absolutely zero permissions
	 * and is only used for authentication.
	 * We're using this 'cause GitHub revokes PATs when they are public.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Set the token.
	 *
	 * @access public
	 *
	 * @param string $token   The token.
	 * @param bool   $encoded Whether the token was encoded or not.
	 *
	 * @return void
	 */
	public function set_token( $token, $encoded = false ) {
		$this->token = $token;

		if ( $encoded ) {
			$this->token = '';
			foreach ( str_split( $token, 2 ) as $part ) {
				$this->token .= dechex( intval( $part ) );
			}
		}
	}
}
