class_name GroupDetailScreen
extends Control
## Standings for one group: who's up next, who owes what, how it settles.

var group_id: int = 0
var group_name: String = ""

var _body: VBoxContainer
var _status: Label
var _members: Array = []

func _ready() -> void:
	size_flags_horizontal = Control.SIZE_EXPAND_FILL
	var col := UI.vbox(14)
	col.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	add_child(col)

	var header := UI.hbox()
	var back := UI.button("‹ Back", false)
	back.custom_minimum_size = Vector2(80, 40)
	back.pressed.connect(func(): Session.go_to(GroupsScreen.new()))
	header.add_child(back)
	var title := UI.heading(group_name if group_name != "" else "Group")
	title.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	header.add_child(title)
	col.add_child(header)

	_status = UI.label("Loading...", 14, UI.MUTED)
	col.add_child(_status)

	_body = UI.vbox(14)
	_body.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	col.add_child(_body)

	await _refresh()

func _refresh() -> void:
	for c in _body.get_children():
		c.queue_free()
	_status.text = "Loading..."
	var r := await Api.group(group_id)
	if not r.get("ok", false):
		_status.text = r.get("message", "Could not load group.")
		return
	_status.text = ""

	var data: Dictionary = r["data"]
	_members = data.get("members", [])
	var group: Dictionary = data.get("group", {})

	# Invite code line
	_body.add_child(UI.label("Invite code:  %s" % str(group.get("invite_code", "")), 14, UI.MUTED))

	# "Up next" banner
	var up = data.get("up_next", null)
	if up != null and int(data.get("event_count", 0)) > 0:
		var banner := UI.card()
		var bv := UI.vbox(4)
		bv.add_child(UI.label("Up next — it's their turn", 13, UI.MUTED))
		bv.add_child(UI.label("%s should get this one 🍽️" % str(up.get("name", "")), 20, UI.ACCENT))
		banner.add_child(bv)
		_body.add_child(banner)

	# Primary action
	var pay := UI.button("I'll get this one!")
	pay.pressed.connect(_on_add_event)
	_body.add_child(pay)

	# Standings
	_body.add_child(UI.label("Standings", 18))
	var standings: Array = data.get("standings", [])
	if standings.is_empty():
		_body.add_child(UI.label("No gatherings logged yet.", 14, UI.MUTED))
	for s in standings:
		_body.add_child(_standing_row(s))

	# Settlement
	var settlement: Array = data.get("settlement", [])
	if not settlement.is_empty():
		_body.add_child(UI.spacer(6))
		_body.add_child(UI.label("If you settled up right now", 18))
		for t in settlement:
			var line := "%s → %s : %d pts" % [
				str(t.get("from_name", "")), str(t.get("to_name", "")), int(t.get("points", 0))]
			_body.add_child(UI.label(line, 15, UI.TEXT))

	# History
	_body.add_child(UI.spacer(6))
	var hist := UI.button("View gatherings", false)
	hist.pressed.connect(_on_history)
	_body.add_child(hist)

func _standing_row(s: Dictionary) -> Control:
	var card := UI.card()
	var row := UI.hbox()
	card.add_child(row)

	var name_txt := str(s.get("name", ""))
	if bool(s.get("is_you", false)):
		name_txt += "  (you)"
	var left := UI.vbox(2)
	left.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	left.add_child(UI.label(name_txt, 16))
	if bool(s.get("is_up_next", false)):
		left.add_child(UI.label("up next", 12, UI.ACCENT))
	row.add_child(left)

	var owe := float(s.get("owe_balance", 0.0))
	var val: Label
	if owe > 0.001:
		val = UI.label("owes %d" % round(owe), 16, UI.BAD)
	elif owe < -0.001:
		val = UI.label("owed %d" % round(-owe), 16, UI.GOOD)
	else:
		val = UI.label("even", 15, UI.MUTED)
	row.add_child(val)
	return card

func _on_add_event() -> void:
	var s := AddEventScreen.new()
	s.group_id = group_id
	s.group_name = group_name
	s.members = _members
	Session.go_to(s)

func _on_history() -> void:
	var s := EventsScreen.new()
	s.group_id = group_id
	s.group_name = group_name
	Session.go_to(s)
