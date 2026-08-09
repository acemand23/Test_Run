class_name AddEventScreen
extends Control
## Log a gathering: who paid, and everyone's estimate in points.

var group_id: int = 0
var group_name: String = ""
var members: Array = []            # [{id, name, ...}, ...]

var _desc: LineEdit
var _date: LineEdit
var _payer: OptionButton
var _status: Label
var _submit: Button
var _point_fields: Dictionary = {}  # user_id -> SpinBox
var _total_label: Label

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
	var title := UI.heading("I'll get this one")
	title.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	header.add_child(title)
	col.add_child(header)

	col.add_child(UI.label("What was it?", 13, UI.MUTED))
	_desc = UI.input("Dinner at Luigi's")
	col.add_child(_desc)

	col.add_child(UI.label("Date", 13, UI.MUTED))
	_date = UI.input("YYYY-MM-DD")
	_date.text = Time.get_date_string_from_system()
	col.add_child(_date)

	col.add_child(UI.label("Who paid?", 13, UI.MUTED))
	_payer = OptionButton.new()
	_payer.custom_minimum_size = Vector2(0, 44)
	for m in members:
		var mid := int(m.get("id", 0))
		var mname := str(m.get("name", ""))
		if mid == int(Session.user.get("id", -1)):
			mname += " (you)"
		_payer.add_item(mname, mid)
	# Default the payer to the current user.
	for i in _payer.item_count:
		if _payer.get_item_id(i) == int(Session.user.get("id", -1)):
			_payer.select(i)
	col.add_child(_payer)

	col.add_child(UI.spacer(4))
	col.add_child(UI.label("Everyone's estimate (points)", 15))

	for m in members:
		col.add_child(_estimate_row(m))

	_total_label = UI.label("Total: 0 pts", 15, UI.MUTED)
	col.add_child(_total_label)

	_submit = UI.button("Save gathering")
	_submit.pressed.connect(_on_submit)
	col.add_child(_submit)

	_status = UI.label("", 14, UI.BAD)
	col.add_child(_status)

func _estimate_row(m: Dictionary) -> Control:
	var row := UI.hbox()
	var name := UI.label(str(m.get("name", "")), 16)
	name.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	row.add_child(name)

	var spin := SpinBox.new()
	spin.min_value = 0
	spin.max_value = 100000
	spin.step = 1
	spin.value = 0
	spin.custom_minimum_size = Vector2(120, 40)
	spin.value_changed.connect(func(_v): _update_total())
	row.add_child(spin)

	_point_fields[int(m.get("id", 0))] = spin
	return row

func _update_total() -> void:
	var total := 0
	for uid in _point_fields:
		total += int(_point_fields[uid].value)
	_total_label.text = "Total: %d pts" % total

func _on_submit() -> void:
	var shares: Array = []
	for uid in _point_fields:
		var pts := int(_point_fields[uid].value)
		if pts > 0:
			shares.append({"user_id": uid, "points": pts})

	if shares.is_empty():
		_status.text = "Enter at least one estimate above 0."
		return

	var payer_id := _payer.get_selected_id()
	_submit.disabled = true
	_submit.text = "Saving..."
	var r := await Api.add_event(group_id, payer_id, _desc.text.strip_edges(),
		_date.text.strip_edges(), shares)
	_submit.disabled = false
	_submit.text = "Save gathering"

	if r.get("ok", false):
		_go_back()
	else:
		_status.text = r.get("message", "Could not save.")

func _go_back() -> void:
	var s := GroupDetailScreen.new()
	s.group_id = group_id
	s.group_name = group_name
	Session.go_to(s)
