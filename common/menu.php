<?php

$menu_registry = array();

function menu_register($items) {
	foreach ($items as $url => $item) {
		$GLOBALS['menu_registry'][$url] = $item;
	}
}

function menu_execute_active_handler() {
	$query = (array) explode('/', $_GET['q']);
	$GLOBALS['page'] = $query[0];
	$page = $GLOBALS['menu_registry'][$GLOBALS['page']];
	if (!$page) {
		// header('HTTP/1.0 404 Not Found');
		// die('404 - Page not found.');
		$GLOBALS['page'] = "user";
		$page = $GLOBALS['menu_registry'][$GLOBALS['page']];
		array_unshift($query, "user");
	}

	if ($page['security'])
	user_ensure_authenticated();

	if (function_exists('config_log_request'))
	config_log_request();

	if (function_exists($page['callback']))
	return call_user_func($page['callback'], $query);

	return false;
}

function menu_current_page() {
	return $GLOBALS['page'];
}

function menu_visible_items() {
	static $items;
	if (!isset($items)) {
		$items = array();
		foreach ($GLOBALS['menu_registry'] as $url => $page) {
			if ($page['security'] && !user_is_authenticated()) continue;
			if ($page['hidden']) continue;
			$items[$url] = $page;
		}
	}
	return $items;
}

// function theme_menu_top() {
// 	return theme('menu_both', 'top');
// }

function theme_menu_bottom_button() {
	//	Trim the first slash
	$request = htmlspecialchars(ltrim($_SERVER['REQUEST_URI'],'/'));
	return '<a href="'. SERVER_NAME . $request . '#menu" class="button">'._(LINK_MENU_BUTTON).'</a>';
}

// function theme_menu_both($menu) {
function theme_menu_top() {
	$links = array();
	foreach (menu_visible_items() as $url => $page) {
		$title = $url ? $url : 'home';
		$title = ucwords(str_replace("-", " ", $title));

		if ('yes' == setting_fetch('dabr_show_icons',"yes"))
		{
			$display = $page['display'];
			$class = "menu";
		} else {
			$display = $title . " |";
			$class = "menu-text";
		}
		if (!$url) $url = BASE_URL; // Shouldn't be required, due to <base> element but some browsers are stupid.

		$links[] = "<a href=\"{$url}\" title=\"{$title}\">$display</a>"	;

	}
	// if (user_is_authenticated()) {
	// 	// $user = user_current_username();
	// 	// array_unshift($links, "<b><a href='user/$user'>$user</a></b>");
	// }
	// if ($menu == 'bottom') {
	// 	// $links[] = "<a href='{$_GET['q']}' accesskey='5'>refresh</a> 5";
	// }

	if ('yes' == setting_fetch('dabr_float_menu',"yes")){
		//	Horrible hack to make the height of the element corrent
		$padding = "<div class='{$class}' id='menu'>".implode('&ensp;', $links)."</div>";

		$class .=' menu-float';
		return "<div class='{$class}' id='menu-float'>".implode('&ensp;', $links)."</div>".$padding;
	}
	return "<div class='{$class}' id='menu'>".implode('&ensp;', $links)."</div>";
}
