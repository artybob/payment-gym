wrk.method = "POST"
wrk.body = '{"merchant_id":"load_test","amount":99.99,"currency":"USD"}'
wrk.headers["Content-Type"] = "application/json"
