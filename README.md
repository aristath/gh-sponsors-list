# GitHub Sponsors List for WordPress

So, you're a WordPress developer and you have a [GitHub Sponsors](https://github.com/sponsors/) account? Great. If you don't already have one, you can create it on [github.com/sponsors](https://github.com/sponsors/).  
Now that you have your account and a few sponsors, you want to show your appreciation and include them as sponsors in all your open-source projects.  
This is where this simple class comes in. It retrieves your sponsors from the GitHub Sponsors API, and prints them wherever you want.

## Step 1: Create a new Github token.

First of all you're going to need a new Personal Access Token on GitHub. To create one, go to [github.com/settings/tokens](https://github.com/settings/tokens) and create a new token.  
**IMPORTANT: Do not add any permissions to your token**.
The token you create will be used for API authentication and doesn't serve any other purpose. As such, it does not require any permissions. I'd advise you not to add any permissions to your token.

Copy this token, we'll need it later.

## Step 2: Encode the token if your plugin/theme is on GitHub

As a security precaution, GitHub automatically revokes tokens if they are found in a public repository. You will therefore need to encode it so you can add it to your product without GitHub complaining.  
To do that, open the console in your browser and paste the following inside it, replacing `'YOUR_TOKEN_HERE'` with your actual token:

```js
function convert_token( token ) {
  let result = '';
  token.split( '' ).forEach( function( char ) {
		let charHex = parseInt( char, 16 ).toString();
		charHex = 1 === charHex.length ? '0' + charHex : charHex;
		result += charHex;
	});
	console.log( result );
}
convert_token( 'YOUR_TOKEN_HERE' );
```

Once you do that, you'll see your encoded token printed in the console. Got it? Great. Copy it, we'll need it later.

## Step 3: Add a function to print the sponsors

We'll now create a new function to print the sponsors.

```php
/**
 * Print GitHub Sponsors.
 *
 * @return void
 */
function my_plugin_or_theme_the_github_sponsors() {

	// Require the class.
	if ( ! class_exists( '\Aristath\GHSponsors' ) ) {
		require_once 'gh-sponsors-dashboard/Sponsors.php';
	}

	// Init the object.
	$sponsors = new \Aristath\GHSponsors();

	// Set the token.
	$sponsors->set_token( 'MY_ENCODED_TOKEN', true );

	// Set the username.
	$sponsors->set_gh_username( 'aristath' );

	// Uncomment this line to not add CSS.
	/* $sponsors->add_styles = true; */

	// Manually inject a sponsor.
	$sponsors->add_sponsor(
		[
			'name'    => 'aristath',
			'url'     => 'https://aristath.github.io',
			'img'     => 'https://avatars0.githubusercontent.com/u/588688?s=460&u=b2865bad64212673fc4ab425231e0a61aa9a2193&v=4',
			'classes' => 'round',
		]
	);

	// Get GitHub Sponsors from the API.
	$sponsors->add_sponsors_from_api();

	// Print the sponsors.
	$sponsors->the_sponsors_details();	
}
```

Things to note:
* Obviously `my_plugin_or_theme_the_github_sponsors` is a bad name for a function, choose something nice.
* In the `$sponsors->set_token()` call, replace `MY_ENCODED_TOKEN` with the encoded token you got in step 2.
* The `$sponsirs->add_sponsor()` call can be used to manually inject a sponsor. Perhaps your company or a cousin is sponsoring you, but not via GitHub Sponsors. It's cool if you show your appreciation, this is how you do it.

## Step 4:

Call the function you created anywhere. You can add it in your plugin or theme's admin page, its about page or anywhere else you want.

-------------------

Everything I build is open-source ([see pledge](https://aristath.github.io/blog/pledge)). If you like this project, it helps you out in any way and want to support me, you can become a sponsor. Every little bit helps, you can visit [github.com/sponsors/aristath](https://github.com/sponsors/aristath/) to help out and show your appreciation.