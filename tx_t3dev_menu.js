/***************************************************************
*  Copyright notice
*
*  (c) 2007 Ingo Renner <ingo@typo3.org>
*  (c) 2008 Frank Nägler <typo3@naegler.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * class to handle the clear cache menu
 *
 * $Id$
 */
var T3devLinksMenu = Class.create({

	/**
	 * registers for resize event listener and executes on DOM ready
	 */
	initialize: function() {
		Event.observe(window, 'resize', this.positionMenu);

		Event.observe(window, 'load', function(){
			this.positionMenu();
			this.toolbarItemIcon = $$('#t3dev-links-menu .toolbar-item img')[0].src;

			Event.observe('t3dev-links-menu', 'click', this.toggleMenu);

				// observe all clicks on clear cache actions in
				// the menu
			$$('#t3dev-links-menu li a').each(function(element) {
				Event.observe(element, 'click', this.clickItem.bind(this));
			}.bindAsEventListener(this));
			
			$$('#t3dev-links-menu li.section').each(function(element) {
				Event.observe(element, 'click', this.clickSection.bind(this));
			}.bindAsEventListener(this));
			
			this.closeAllSections();
		}.bindAsEventListener(this));
	},

	/**
	 * positions the menu below the toolbar icon, let's do some math!
	 */
	positionMenu: function() {
		var calculatedOffset = 0;
		var parentWidth      = $('t3dev-links-menu').getWidth();
		var ownWidth         = $$('#t3dev-links-menu ul')[0].getWidth();
		var parentSiblings   = $('t3dev-links-menu').previousSiblings();

		parentSiblings.each(function(toolbarItem) {
			calculatedOffset += toolbarItem.getWidth() - 1;
			// -1 to compensate for the margin-right -1px of the
			// list items,
			// which itself is necessary for overlaying the
			// separator with the active state background

			if(toolbarItem.down().hasClassName('no-separator')) {
				calculatedOffset -= 1;
			}
		});
		calculatedOffset = calculatedOffset - ownWidth + parentWidth;


		$$('#t3dev-links-menu ul')[0].setStyle({
			left: calculatedOffset + 'px'
		});
	},

	/**
	 * toggles the visibility of the menu and places it under the toolbar
	 * icon
	 */
	toggleMenu: function(event) {
		var toolbarItem = $$('#t3dev-links-menu > a')[0];
		var menu        = $$('#t3dev-links-menu .toolbar-item-menu')[0];
		toolbarItem.blur();

		if(!toolbarItem.hasClassName('toolbar-item-active')) {
			toolbarItem.addClassName('toolbar-item-active');
			Effect.Appear(menu, {duration: 0.2});
			TYPO3BackendToolbarManager.hideOthers(toolbarItem);
		} else {
			toolbarItem.removeClassName('toolbar-item-active');
			Effect.Fade(menu, {duration: 0.1});
		}

		if (event) {
			Event.stop(event);
		}
	},

	/**
	 * calls the actual clear cache URL using an asynchronious HTTP request
	 *
	 * @param	Event	prototype event object
	 */
	clickItem: function(event) {
		var toolbarItemIcon = $$('#t3dev-links-menu .toolbar-item img')[0];

		if (event) {
			var url = Event.element(event).href;
			if (url && (url.indexOf('tp://') == -1)) {
				toolbarItemIcon.src = 'gfx/spinner.gif';
				new Ajax.Request(url, {
					'method': 'get',
					'onComplete': function() {
						toolbarItemIcon.src = this.toolbarItemIcon;
					}.bind(this)
				});
			}
			Event.stop(event);
		}
		this.toggleMenu();
	},

	clickSection: function(event) {
		if (event) {
			var id = Event.element(event).id;
			if (Event.element(event).hasClassName('t3dev-section-open')) {
				Event.element(event).addClassName('t3dev-section-close');
				Event.element(event).removeClassName('t3dev-section-open');
			} else {
				Event.element(event).addClassName('t3dev-section-open');
				Event.element(event).removeClassName('t3dev-section-close');
			}
			$$('#t3dev-links-menu li.'+id).each(function(element) {
				if (!element.hasClassName('t3dev-hidden')) {
					element.addClassName('t3dev-hidden');
					Effect.Fade(element, {duration: 0.1});
				} else {
					element.removeClassName('t3dev-hidden');
					Effect.Appear(element, {duration: 0.1});
				}
			});
		}
		Event.stop(event);
	},
	
	closeAllSections: function() {
		$$('#t3dev-links-menu li.section span').each(function(element) {
			var id = element.id;
			if (id && (id != 'section0')) {
				element.addClassName('t3dev-section-close');
				$$('#t3dev-links-menu li.'+id).each(function(element) {
					element.addClassName('t3dev-hidden');
					Effect.Fade(element, {duration: 0.1});
				});
			} else {
				element.addClassName('t3dev-section-open');
			}
		});
	}
});

var TYPO3BackendT3devLinksMenu = new T3devLinksMenu();

