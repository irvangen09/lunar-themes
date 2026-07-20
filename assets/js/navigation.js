/**
 * Header navigation behaviour.
 *
 * Desktop dropdowns already work from CSS alone (:hover and
 * :focus-within reveal a ".sub-menu" — see layout.css), so this file
 * only adds what CSS cannot do on its own:
 *
 * 1. Opening/closing the mobile nav panel via the hamburger button.
 * 2. An accordion toggle for menu items with children, active only
 *    while the mobile panel is in use — on wider screens the same
 *    links keep their normal hover/keyboard behaviour untouched.
 *
 * @package Lunar
 */

( function () {
	'use strict';

	var MOBILE_QUERY = '( max-width: 640px )';

	var navToggle = document.querySelector( '.lunar-nav-toggle' );
	var navSlot = document.getElementById( 'lunar-nav-slot' );

	/**
	 * Closes the mobile nav panel and resets the toggle button state.
	 */
	function closeNavPanel() {
		if ( navSlot ) {
			navSlot.classList.remove( 'is-open' );
		}

		if ( navToggle ) {
			navToggle.setAttribute( 'aria-expanded', 'false' );
		}
	}

	if ( navToggle && navSlot ) {
		navToggle.addEventListener( 'click', function () {
			var isOpen = navSlot.classList.toggle( 'is-open' );

			navToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	}

	var parentLinks = document.querySelectorAll(
		'.lunar-site-nav__list .menu-item-has-children > a'
	);

	parentLinks.forEach( function ( link ) {
		link.setAttribute( 'aria-expanded', 'false' );

		link.addEventListener( 'click', function ( event ) {
			// On wider screens the dropdown already opens on hover or
			// keyboard focus, so clicking the link keeps navigating
			// normally there. Only the mobile accordion needs the
			// click intercepted.
			if ( ! window.matchMedia( MOBILE_QUERY ).matches ) {
				return;
			}

			event.preventDefault();

			var item = link.parentElement;
			var isOpen = item.classList.toggle( 'is-open' );

			link.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	} );

	// Resets the panel if the viewport is resized past the mobile
	// breakpoint (e.g. rotating a tablet), so nothing stays stuck open.
	window.matchMedia( MOBILE_QUERY ).addEventListener( 'change', function ( query ) {
		if ( ! query.matches ) {
			closeNavPanel();
		}
	} );
} )();