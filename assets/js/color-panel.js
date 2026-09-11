(function () {
	'use strict';

	var cfg = window.atacColorPanel;
	if (!cfg) {
		return;
	}

	var state = Object.assign({}, cfg.colors);
	var panelOpen = false;
	var styleEl = null;

	function $(sel, root) {
		return (root || document).querySelector(sel);
	}

	function ensureStyleEl() {
		styleEl = document.getElementById('atac-brand-vars');
		if (!styleEl) {
			styleEl = document.createElement('style');
			styleEl.id = 'atac-brand-vars';
			document.head.appendChild(styleEl);
		}
		return styleEl;
	}

	function buildCss(colors, apply) {
		var vars =
			':root{--atac-menu-bg:' +
			colors.menu_bg +
			';--atac-menu-text:' +
			colors.menu_text +
			';--atac-menu-highlight:' +
			colors.menu_highlight +
			';--atac-admin-bar:' +
			colors.admin_bar +
			';--atac-primary:' +
			colors.primary_button +
			';--atac-link:' +
			colors.link +
			';}';

		if (!apply) {
			return vars;
		}

		return (
			vars +
			'#wpadminbar{background:var(--atac-admin-bar)!important}' +
			'#wpadminbar .ab-item,#wpadminbar a.ab-item,#wpadminbar>#wp-toolbar span.ab-label,#wpadminbar>#wp-toolbar span.noticon{color:#fff!important}' +
			'#adminmenuback,#adminmenuwrap,#adminmenu{background:var(--atac-menu-bg)!important}' +
			'#adminmenu a{color:var(--atac-menu-text)!important}' +
			'#adminmenu div.wp-menu-image:before{color:var(--atac-menu-text)!important}' +
			'#adminmenu li.menu-top:hover,#adminmenu li.opensub>a.menu-top,#adminmenu li>a.menu-top:focus{background:var(--atac-menu-highlight)!important;color:#fff!important}' +
			'#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.current a.menu-top,#adminmenu .wp-menu-arrow,#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head{background:var(--atac-menu-highlight)!important}' +
			'#adminmenu .wp-submenu{background:#1a1d20!important}' +
			'body.wp-core-ui .button-primary{background:var(--atac-primary)!important;border-color:var(--atac-primary)!important;color:#fff!important}' +
			'body.wp-core-ui .button-primary:hover,body.wp-core-ui .button-primary:focus{filter:brightness(1.08)}' +
			'a,body a{color:var(--atac-link)}' +
			'#adminmenu .awaiting-mod,#adminmenu .update-plugins{background:var(--atac-primary)!important}'
		);
	}

	function isDefault(colors) {
		var d = cfg.defaults;
		return (
			colors.menu_bg === d.menu_bg &&
			colors.menu_text === d.menu_text &&
			colors.menu_highlight === d.menu_highlight &&
			colors.admin_bar === d.admin_bar &&
			colors.primary_button === d.primary_button &&
			colors.link === d.link
		);
	}

	function applyPreview(colors) {
		ensureStyleEl().textContent = buildCss(colors, !isDefault(colors));
		updateFab(colors);
		syncInputs(colors);
		markActivePreset(colors);
	}

	function updateFab(colors) {
		var fab = $('#atac-color-fab');
		if (!fab) {
			return;
		}
		fab.style.background = colors.menu_highlight || colors.primary_button || '#1d2327';
	}

	function syncInputs(colors) {
		Object.keys(cfg.labels).forEach(function (key) {
			var colorInput = document.getElementById('atac-color-' + key);
			var textInput = document.getElementById('atac-hex-' + key);
			if (colorInput) {
				colorInput.value = normalizeHex(colors[key]);
			}
			if (textInput) {
				textInput.value = normalizeHex(colors[key]);
			}
		});
	}

	function normalizeHex(value) {
		if (!value) {
			return '#000000';
		}
		value = String(value).trim();
		if (/^#[0-9a-fA-F]{3}$/.test(value)) {
			return (
				'#' +
				value[1] +
				value[1] +
				value[2] +
				value[2] +
				value[3] +
				value[3]
			).toLowerCase();
		}
		if (/^#[0-9a-fA-F]{6}$/.test(value)) {
			return value.toLowerCase();
		}
		return '#000000';
	}

	function markActivePreset(colors) {
		var buttons = document.querySelectorAll('.atac-preset');
		buttons.forEach(function (btn) {
			var id = btn.getAttribute('data-preset');
			var preset = cfg.presets[id];
			var match = preset && JSON.stringify(preset.colors) === JSON.stringify(colors);
			btn.classList.toggle('is-active', !!match);
		});
	}

	function setStatus(message, isError) {
		var el = $('.atac-panel-status');
		if (!el) {
			return;
		}
		el.textContent = message || '';
		el.classList.toggle('is-error', !!isError);
	}

	function buildPanel() {
		var root = document.getElementById('atac-color-root');
		if (!root || root.dataset.ready) {
			return;
		}
		root.hidden = false;
		root.dataset.ready = '1';

		var fab = document.createElement('button');
		fab.type = 'button';
		fab.id = 'atac-color-fab';
		fab.setAttribute('aria-label', cfg.i18n.openAria);
		fab.innerHTML =
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#fff" d="M12 3a9 9 0 0 0 0 18c.7 0 1.2-.5 1.2-1.2 0-.3-.1-.6-.3-.8-.2-.2-.3-.5-.3-.8 0-.7.5-1.2 1.2-1.2H16a5 5 0 0 0 0-10h-.5A9 9 0 0 0 12 3zm-5.5 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3-4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm3 4a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>';
		fab.addEventListener('click', togglePanel);

		var panel = document.createElement('div');
		panel.id = 'atac-color-panel';
		panel.hidden = true;
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-modal', 'false');
		panel.setAttribute('aria-label', cfg.i18n.title);

		var presetHtml = Object.keys(cfg.presets)
			.map(function (id) {
				var p = cfg.presets[id];
				var swatches = ['menu_bg', 'menu_highlight', 'primary_button', 'link']
					.map(function (k) {
						return '<span style="background:' + p.colors[k] + '"></span>';
					})
					.join('');
				return (
					'<button type="button" class="atac-preset" data-preset="' +
					id +
					'">' +
					'<span class="atac-preset-label">' +
					escapeHtml(p.label) +
					'</span>' +
					'<span class="atac-preset-swatches">' +
					swatches +
					'</span>' +
					'</button>'
				);
			})
			.join('');

		var fieldsHtml = Object.keys(cfg.labels)
			.map(function (key) {
				return (
					'<div class="atac-field">' +
					'<label for="atac-color-' +
					key +
					'">' +
					escapeHtml(cfg.labels[key]) +
					'</label>' +
					'<div class="atac-field-controls">' +
					'<input type="color" id="atac-color-' +
					key +
					'" data-key="' +
					key +
					'" value="' +
					normalizeHex(state[key]) +
					'" />' +
					'<input type="text" id="atac-hex-' +
					key +
					'" data-key="' +
					key +
					'" maxlength="7" value="' +
					normalizeHex(state[key]) +
					'" />' +
					'</div></div>'
				);
			})
			.join('');

		panel.innerHTML =
			'<div class="atac-panel-header">' +
			'<div><h2>' +
			escapeHtml(cfg.i18n.title) +
			'</h2><p>' +
			escapeHtml(cfg.i18n.subtitle) +
			'</p></div>' +
			'<button type="button" class="atac-panel-close" aria-label="' +
			escapeHtml(cfg.i18n.close) +
			'">×</button>' +
			'</div>' +
			'<div class="atac-panel-body">' +
			'<div class="atac-panel-section-title">' +
			escapeHtml(cfg.i18n.starters) +
			'</div>' +
			'<div class="atac-presets">' +
			presetHtml +
			'</div>' +
			'<div class="atac-panel-section-title">' +
			escapeHtml(cfg.i18n.yourColors) +
			'</div>' +
			'<div class="atac-fields">' +
			fieldsHtml +
			'</div>' +
			'<div class="atac-panel-actions">' +
			'<button type="button" class="button button-primary" id="atac-save-colors">' +
			escapeHtml(cfg.i18n.save) +
			'</button>' +
			'<button type="button" class="button" id="atac-reset-colors">' +
			escapeHtml(cfg.i18n.reset) +
			'</button>' +
			'</div>' +
			'<div class="atac-panel-status" aria-live="polite"></div>' +
			'</div>';

		root.appendChild(fab);
		root.appendChild(panel);

		panel.querySelector('.atac-panel-close').addEventListener('click', closePanel);

		panel.querySelectorAll('.atac-preset').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-preset');
				state = Object.assign({}, cfg.presets[id].colors);
				applyPreview(state);
				setStatus('');
			});
		});

		panel.querySelectorAll('input[type="color"]').forEach(function (input) {
			input.addEventListener('input', function () {
				var key = input.getAttribute('data-key');
				state[key] = normalizeHex(input.value);
				applyPreview(state);
				setStatus('');
			});
		});

		panel.querySelectorAll('input[type="text"]').forEach(function (input) {
			input.addEventListener('change', function () {
				var key = input.getAttribute('data-key');
				var hex = normalizeHex(input.value);
				state[key] = hex;
				applyPreview(state);
				setStatus('');
			});
		});

		$('#atac-save-colors').addEventListener('click', saveColors);
		$('#atac-reset-colors').addEventListener('click', resetColors);

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && panelOpen) {
				closePanel();
			}
		});

		var settingsBtn = document.getElementById('atac-open-color-panel');
		if (settingsBtn) {
			settingsBtn.addEventListener('click', openPanel);
		}

		applyPreview(state);
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function openPanel() {
		var panel = $('#atac-color-panel');
		if (!panel) {
			return;
		}
		panel.hidden = false;
		panelOpen = true;
		setStatus('');
	}

	function closePanel() {
		var panel = $('#atac-color-panel');
		if (!panel) {
			return;
		}
		panel.hidden = true;
		panelOpen = false;
	}

	function togglePanel() {
		if (panelOpen) {
			closePanel();
		} else {
			openPanel();
		}
	}

	function post(action, extra) {
		var body = new FormData();
		body.append('action', action);
		body.append('nonce', cfg.nonce);
		if (extra) {
			Object.keys(extra).forEach(function (k) {
				body.append(k, extra[k]);
			});
		}
		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body,
		}).then(function (res) {
			return res.json();
		});
	}

	function saveColors() {
		var btn = $('#atac-save-colors');
		btn.disabled = true;
		btn.textContent = cfg.i18n.saving;
		post('atac_save_brand_colors', { colors: JSON.stringify(state) })
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error('save failed');
				}
				state = Object.assign({}, json.data.colors);
				cfg.colors = state;
				ensureStyleEl().textContent = json.data.css;
				applyPreview(state);
				setStatus(cfg.i18n.saved, false);
			})
			.catch(function () {
				setStatus(cfg.i18n.error, true);
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = cfg.i18n.save;
			});
	}

	function resetColors() {
		post('atac_reset_brand_colors')
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error('reset failed');
				}
				state = Object.assign({}, json.data.colors);
				cfg.colors = state;
				ensureStyleEl().textContent = json.data.css;
				applyPreview(state);
				setStatus(cfg.i18n.resetDone, false);
			})
			.catch(function () {
				setStatus(cfg.i18n.error, true);
			});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', buildPanel);
	} else {
		buildPanel();
	}
})();
