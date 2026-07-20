/**
 * Header navigation behaviour.
 *
 * Desktop dropdowns already work from CSS alone (:hover and
 * :focus-within reveal a ".sub-menu" — see layout.css). This file adds
 * what CSS cannot do on its own:
 *
 * 1. Opening/closing the mobile off-canvas nav panel (hamburger button,
 *    overlay click, its own close button, and the Escape key).
 * 2. An accordion toggle for menu items with children, active only
 *    while the mobile panel is in use — on wider screens the same
 *    links keep their normal hover/keyboard behaviour untouched.
 * 3. Keeping only one submenu open at a time inside the mobile panel.
 *
 * @package Lunar
 */

( function () {
	'use strict';

	var MOBILE_QUERY = '( max-width: 640px )';
	var BODY_LOCK_CLASS = 'lunar-no-scroll';

	var navToggle = document.querySelector( '.lunar-nav-toggle' );
	var navSlot = document.getElementById( 'lunar-nav-slot' );
	var navOverlay = document.getElementById( 'lunar-nav-overlay' );
	var navClose = document.querySelector( '.lunar-nav-close' );

	/**
	 * Opens or closes the mobile nav panel and keeps every related
	 * piece of state (overlay, toggle button, body scroll lock) in
	 * sync in one place.
	 *
	 * @param {boolean} isOpen Whether the panel should be open.
	 */
	function setPanelOpen( isOpen ) {
		if ( navSlot ) {
			navSlot.classList.toggle( 'is-open', isOpen );
		}

		if ( navOverlay ) {
			navOverlay.classList.toggle( 'is-open', isOpen );
		}

		if ( navToggle ) {
			navToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		}

		document.body.classList.toggle( BODY_LOCK_CLASS, isOpen );
	}

	if ( navToggle && navSlot ) {
		navToggle.addEventListener( 'click', function () {
			setPanelOpen( ! navSlot.classList.contains( 'is-open' ) );
		} );
	}

	if ( navClose ) {
		navClose.addEventListener( 'click', function () {
			setPanelOpen( false );
		} );
	}

	if ( navOverlay ) {
		navOverlay.addEventListener( 'click', function () {
			setPanelOpen( false );
		} );
	}

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && navSlot && navSlot.classList.contains( 'is-open' ) ) {
			setPanelOpen( false );
		}
	} );

	/**
	 * Closes every open sibling submenu next to the given item, so
	 * opening a new one always leaves just one expanded at a time.
	 *
	 * @param {Element} currentItem The <li> about to be opened.
	 */
	function closeSiblingSubmenus( currentItem ) {
		var siblings = currentItem.parentElement.children;

		Array.prototype.forEach.call( siblings, function ( sibling ) {
			if ( sibling === currentItem || ! sibling.classList.contains( 'is-open' ) ) {
				return;
			}

			sibling.classList.remove( 'is-open' );

			var siblingLink = sibling.querySelector( ':scope > a' );

			if ( siblingLink ) {
				siblingLink.setAttribute( 'aria-expanded', 'false' );
			}
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
			var willOpen = ! item.classList.contains( 'is-open' );

			if ( willOpen ) {
				closeSiblingSubmenus( item );
			}

			item.classList.toggle( 'is-open', willOpen );
			link.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
		} );
	} );

	// Resets the panel if the viewport is resized past the mobile
	// breakpoint (e.g. rotating a tablet), so nothing stays stuck open.
	window.matchMedia( MOBILE_QUERY ).addEventListener( 'change', function ( query ) {
		if ( ! query.matches ) {
			setPanelOpen( false );
		}
	} );
} )();