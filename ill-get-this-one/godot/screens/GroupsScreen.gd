class_name GroupsScreen
extends Control
## Lists the groups you belong to, with your standing in each.

var _list: VBoxContainer
var _status: Label

func _ready() -> void:
	size_flags_horizontal = Control.SIZE_EXPAND_FILL
	var col := UI.vbox(14)
	col.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	add_child(col)

	var header := UI.hbox()
	var title := UI.heading("Your Groups")
	title.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	header.add_child(title)
	var logout := UI.button("Log out", false)
	logout.custom_minimum_size = Vector2(90, 40)
	logout.pressed.connect(_on_logout)
	header.add_child(logout)
	col.add_child(header)

	var hi := "Hey %s" % Session.user.get("name", "")
	col.add_child(UI.label(hi, 14, UI.MUTED))

	var actions := UI.hbox()
	var create_btn := UI.button("+ Create")
	create_btn.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	create_btn.pressed.connect(_on_create)
	actions.add_child(create_btn)
	var join_btn := UI.button("Join by code", false)
	join_btn.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	join_btn.pressed.connect(_on_join)
	actions.add_child(join_btn)
	col.add_child(actions)

	_status = UI.label("", 14, UI.MUTED)
	col.add_child(_status)

	_list = UI.vbox(10)
	_list.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	col.add_child(_list)

	await _refresh()

func _refresh() -> void:
	for c in _list.get_children():
		c.queue_free()
	_status.text = "Loading..."
	var r := await Api.my_groups()
	_status.text = ""
	if not r.get("ok", false):
		_status.text = r.get("message", "Could not load groups.")
		return

	var groups: Array = r["data"].get("groups", [])
	if groups.is_empty():
		_status.text = "No groups yet. Create one or join with a code."
		return

	for g in groups:
		_list.add_child(_group_card(g))

func _group_card(g: Dictionary) -> Control:
	var card := UI.card()
	var row := UI.hbox()
	card.add_child(row)

	var left := UI.vbox(4)
	left.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	left.add_child(UI.label(str(g.get("name", "Group")), 18))
	left.add_child(UI.label("%d members  ·  code %s" % [
		int(g.get("member_count", 0)), str(g.get("invite_code", ""))], 13, UI.MUTED))
	row.add_child(left)

	var owe := float(g.get("my_owe_balance", 0.0))
	var right := UI.vbox(2)
	right.alignment = BoxContainer.ALIGNMENT_CENTER
	if owe > 0.001:
		right.add_child(UI.label("you owe", 12, UI.MUTED))
		right.add_child(UI.label("%d pts" % round(owe), 18, UI.BAD))
	elif owe < -0.001:
		right.add_child(UI.label("you're owed", 12, UI.MUTED))
		right.add_child(UI.label("%d pts" % round(-owe), 18, UI.GOOD))
	else:
		right.add_child(UI.label("even", 14, UI.MUTED))
	row.add_child(right)

	var btn := Button.new()
	btn.flat = true
	btn.set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	btn.pressed.connect(func(): _open_group(int(g.get("id", 0)), str(g.get("name", ""))))
	card.add_child(btn)
	return card

func _open_group(gid: int, gname: String) -> void:
	var s := GroupDetailScreen.new()
	s.group_id = gid
	s.group_name = gname
	Session.go_to(s)

func _on_create() -> void:
	_prompt("Create a group", "Group name", func(text):
		if text.strip_edges() == "":
			return
		var r := await Api.create_group(text.strip_edges())
		if r.get("ok", false):
			await _refresh()
		else:
			_status.text = r.get("message", "Could not create group."))

func _on_join() -> void:
	_prompt("Join a group", "8-character invite code", func(text):
		if text.strip_edges() == "":
			return
		var r := await Api.join_group(text.strip_edges())
		if r.get("ok", false):
			await _refresh()
		else:
			_status.text = r.get("message", "Could not join group."))

func _on_logout() -> void:
	await Api.logout()
	Session.go_to(AuthScreen.new())

## Simple modal text prompt using AcceptDialog.
func _prompt(title: String, placeholder: String, on_ok: Callable) -> void:
	var dlg := AcceptDialog.new()
	dlg.title = title
	dlg.ok_button_text = "OK"
	var field := UI.input(placeholder)
	field.custom_minimum_size = Vector2(280, 44)
	dlg.add_child(field)
	dlg.register_text_enter(field)
	dlg.confirmed.connect(func(): on_ok.call(field.text))
	dlg.close_requested.connect(func(): dlg.queue_free())
	add_child(dlg)
	dlg.popup_centered()
	field.grab_focus()
