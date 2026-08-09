class_name EventsScreen
extends Control
## History of gatherings in a group, most recent first.

var group_id: int = 0
var group_name: String = ""

var _list: VBoxContainer
var _status: Label

func _ready() -> void:
	size_flags_horizontal = Control.SIZE_EXPAND_FILL
	var col := UI.vbox(12)
	col.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	add_child(col)

	var header := UI.hbox()
	var back := UI.button("‹ Back", false)
	back.custom_minimum_size = Vector2(80, 40)
	back.pressed.connect(_go_back)
	header.add_child(back)
	var title := UI.heading("Gatherings")
	title.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	header.add_child(title)
	col.add_child(header)

	_status = UI.label("Loading...", 14, UI.MUTED)
	col.add_child(_status)

	_list = UI.vbox(10)
	_list.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	col.add_child(_list)

	await _refresh()

func _refresh() -> void:
	for c in _list.get_children():
		c.queue_free()
	var r := await Api.group_events(group_id)
	if not r.get("ok", false):
		_status.text = r.get("message", "Could not load gatherings.")
		return

	var events: Array = r["data"].get("events", [])
	if events.is_empty():
		_status.text = "No gatherings logged yet."
		return
	_status.text = ""

	for e in events:
		_list.add_child(_event_card(e))

func _event_card(e: Dictionary) -> Control:
	var card := UI.card()
	var v := UI.vbox(6)
	card.add_child(v)

	var top := UI.hbox()
	var desc := str(e.get("description", ""))
	if desc == "":
		desc = "Gathering"
	var t := UI.label(desc, 17)
	t.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	top.add_child(t)
	top.add_child(UI.label("%d pts" % int(e.get("total_points", 0)), 16, UI.ACCENT))
	v.add_child(top)

	v.add_child(UI.label("%s paid  ·  %s" % [
		str(e.get("payer_name", "")), str(e.get("occurred_on", ""))], 13, UI.MUTED))

	var shares: Array = e.get("shares", [])
	if not shares.is_empty():
		var parts: Array = []
		for s in shares:
			parts.append("%s %d" % [str(s.get("name", "")), int(s.get("points", 0))])
		v.add_child(UI.label(", ".join(parts), 13, UI.MUTED))

	return card

func _go_back() -> void:
	var s := GroupDetailScreen.new()
	s.group_id = group_id
	s.group_name = group_name
	Session.go_to(s)
