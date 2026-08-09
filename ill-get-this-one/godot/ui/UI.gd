class_name UI
extends RefCounted
## Small factory of styled Control nodes so screens stay readable.

const BG      := Color("101418")
const CARD    := Color("1b2330")
const CARD2   := Color("232d3d")
const ACCENT  := Color("f5a623")
const TEXT    := Color("e8edf2")
const MUTED   := Color("8a97a8")
const GOOD    := Color("3ecf8e")   # owed to you
const BAD     := Color("ff6b6b")   # you owe

static func label(text: String, size: int = 16, color: Color = TEXT) -> Label:
	var l := Label.new()
	l.text = text
	l.add_theme_font_size_override("font_size", size)
	l.add_theme_color_override("font_color", color)
	l.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	return l

static func heading(text: String) -> Label:
	return label(text, 26, TEXT)

static func button(text: String, primary: bool = true) -> Button:
	var b := Button.new()
	b.text = text
	b.custom_minimum_size = Vector2(0, 46)
	b.add_theme_font_size_override("font_size", 16)
	var normal := _sb()
	normal.bg_color = ACCENT if primary else CARD2
	var hover := normal.duplicate()
	hover.bg_color = (ACCENT.lightened(0.1) if primary else CARD2.lightened(0.08))
	b.add_theme_stylebox_override("normal", normal)
	b.add_theme_stylebox_override("hover", hover)
	b.add_theme_stylebox_override("pressed", normal)
	b.add_theme_color_override("font_color", BG if primary else TEXT)
	b.add_theme_color_override("font_hover_color", BG if primary else TEXT)
	return b

static func input(placeholder: String, secret: bool = false) -> LineEdit:
	var e := LineEdit.new()
	e.placeholder_text = placeholder
	e.secret = secret
	e.custom_minimum_size = Vector2(0, 44)
	e.add_theme_font_size_override("font_size", 16)
	var sb := _sb()
	sb.bg_color = CARD2
	e.add_theme_stylebox_override("normal", sb)
	e.add_theme_stylebox_override("focus", sb)
	e.add_theme_color_override("font_color", TEXT)
	e.add_theme_color_override("font_placeholder_color", MUTED)
	return e

static func vbox(separation: int = 12) -> VBoxContainer:
	var v := VBoxContainer.new()
	v.add_theme_constant_override("separation", separation)
	return v

static func hbox(separation: int = 10) -> HBoxContainer:
	var h := HBoxContainer.new()
	h.add_theme_constant_override("separation", separation)
	return h

static func card() -> PanelContainer:
	var p := PanelContainer.new()
	var sb := _sb()
	sb.bg_color = CARD
	sb.content_margin_left = 16
	sb.content_margin_right = 16
	sb.content_margin_top = 14
	sb.content_margin_bottom = 14
	p.add_theme_stylebox_override("panel", sb)
	return p

static func spacer(height: int = 8) -> Control:
	var c := Control.new()
	c.custom_minimum_size = Vector2(0, height)
	return c

static func _sb(_primary: bool = true) -> StyleBoxFlat:
	var sb := StyleBoxFlat.new()
	sb.corner_radius_top_left = 12
	sb.corner_radius_top_right = 12
	sb.corner_radius_bottom_left = 12
	sb.corner_radius_bottom_right = 12
	sb.content_margin_left = 14
	sb.content_margin_right = 14
	sb.content_margin_top = 10
	sb.content_margin_bottom = 10
	return sb
