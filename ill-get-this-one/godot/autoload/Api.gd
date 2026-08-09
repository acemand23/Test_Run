extends Node
## REST client for the "I'll Get This One" PHP backend.
## Registered as an autoload singleton (see project.godot).
##
## Every call is awaitable and returns a Dictionary:
##   { ok: bool, status: int, data: Dictionary, error: String, message: String }

const CFG_PATH := "user://iggt.cfg"

# Point this at your cPanel API. Overridable at runtime on the login screen.
var base_url: String = "https://your-domain.com/api"
var token: String = ""

func _ready() -> void:
	_load_config()

# ------------------------------------------------------------- config storage
func _load_config() -> void:
	var cfg := ConfigFile.new()
	if cfg.load(CFG_PATH) == OK:
		base_url = cfg.get_value("api", "base_url", base_url)
		token = cfg.get_value("api", "token", "")

func _save_config() -> void:
	var cfg := ConfigFile.new()
	cfg.set_value("api", "base_url", base_url)
	cfg.set_value("api", "token", token)
	cfg.save(CFG_PATH)

func set_base_url(url: String) -> void:
	base_url = url.strip_edges().rstrip("/")
	_save_config()

func set_token(t: String) -> void:
	token = t
	_save_config()

# ------------------------------------------------------------- core request
func _request(method: int, path: String, payload = null) -> Dictionary:
	var http := HTTPRequest.new()
	add_child(http)

	var headers := PackedStringArray(["Content-Type: application/json"])
	if token != "":
		headers.append("Authorization: Bearer " + token)

	var body := ""
	if payload != null:
		body = JSON.stringify(payload)

	var err := http.request(base_url + path, headers, method, body)
	if err != OK:
		http.queue_free()
		return {"ok": false, "status": 0, "data": {},
				"error": "no_connection", "message": "Could not reach the server."}

	var res: Array = await http.request_completed
	http.queue_free()

	var status: int = res[1]
	var text: String = (res[3] as PackedByteArray).get_string_from_utf8()
	var parsed = JSON.parse_string(text)
	if typeof(parsed) != TYPE_DICTIONARY:
		return {"ok": false, "status": status, "data": {},
				"error": "bad_response", "message": "Unexpected server response."}

	var ok: bool = bool(parsed.get("ok", false)) and status >= 200 and status < 300
	return {
		"ok": ok,
		"status": status,
		"data": parsed.get("data", {}),
		"error": parsed.get("error", ""),
		"message": parsed.get("message", ""),
	}

# ------------------------------------------------------------- endpoints
func register(name: String, email: String, password: String) -> Dictionary:
	var r := await _request(HTTPClient.METHOD_POST, "/auth/register",
		{"name": name, "email": email, "password": password})
	_capture_login(r)
	return r

func login(email: String, password: String) -> Dictionary:
	var r := await _request(HTTPClient.METHOD_POST, "/auth/login",
		{"email": email, "password": password})
	_capture_login(r)
	return r

func _capture_login(r: Dictionary) -> void:
	if r.get("ok", false):
		set_token(r["data"].get("token", ""))
		Session.set_user(r["data"].get("user", {}))

func logout() -> Dictionary:
	var r := await _request(HTTPClient.METHOD_POST, "/auth/logout")
	Session.clear()
	return r

func my_groups() -> Dictionary:
	return await _request(HTTPClient.METHOD_GET, "/groups")

func create_group(name: String) -> Dictionary:
	return await _request(HTTPClient.METHOD_POST, "/groups", {"name": name})

func join_group(invite_code: String) -> Dictionary:
	return await _request(HTTPClient.METHOD_POST, "/groups/join",
		{"invite_code": invite_code})

func group(group_id: int) -> Dictionary:
	return await _request(HTTPClient.METHOD_GET, "/groups/%d" % group_id)

func group_events(group_id: int) -> Dictionary:
	return await _request(HTTPClient.METHOD_GET, "/groups/%d/events" % group_id)

func add_event(group_id: int, payer_id: int, description: String,
		occurred_on: String, shares: Array) -> Dictionary:
	return await _request(HTTPClient.METHOD_POST, "/groups/%d/events" % group_id, {
		"payer_id": payer_id,
		"description": description,
		"occurred_on": occurred_on,
		"shares": shares,
	})

func delete_event(group_id: int, event_id: int) -> Dictionary:
	return await _request(HTTPClient.METHOD_DELETE,
		"/groups/%d/events/%d" % [group_id, event_id])
