extends Control
## Root scene. Owns the full-screen background and swaps in one screen at a time.

var _current: Control = null

func _ready() -> void:
	Session.main = self

	# Solid background behind every screen.
	var bg := ColorRect.new()
	bg.color = UI.BG
	bg.set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	bg.mouse_filter = Control.MOUSE_FILTER_IGNORE
	add_child(bg)

	# Decide the first screen based on whether we have a saved token.
	if Session.is_logged_in() or Api.token != "":
		_verify_session_then_start()
	else:
		show_screen(AuthScreen.new())

func _verify_session_then_start() -> void:
	# We have a token from a previous run; confirm it still works.
	var loading := AuthScreen.new()
	show_screen(loading)  # cheap placeholder; will be replaced
	var r := await Api.my_groups()
	if r.get("ok", false):
		show_screen(GroupsScreen.new())
	else:
		Session.clear()
		show_screen(AuthScreen.new())

## Replace whatever is on screen with `screen`, wrapped in a scroll + margins.
func show_screen(screen: Control) -> void:
	if _current and is_instance_valid(_current):
		_current.queue_free()

	var scroll := ScrollContainer.new()
	scroll.set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	scroll.horizontal_scroll_mode = ScrollContainer.SCROLL_MODE_DISABLED

	var margin := MarginContainer.new()
	margin.add_theme_constant_override("margin_left", 20)
	margin.add_theme_constant_override("margin_right", 20)
	margin.add_theme_constant_override("margin_top", 48)
	margin.add_theme_constant_override("margin_bottom", 28)
	margin.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	margin.custom_minimum_size = Vector2(0, 0)

	screen.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	margin.add_child(screen)
	scroll.add_child(margin)

	# Make the inner margin track the viewport width so content fills the phone.
	margin.custom_minimum_size.x = get_viewport_rect().size.x

	add_child(scroll)
	_current = scroll
