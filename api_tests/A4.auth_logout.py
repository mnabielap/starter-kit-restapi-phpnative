import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

refresh_token = utils.load_config("refresh_token")

if not refresh_token:
    print("[ERROR] No refresh_token found in secrets.json. Cannot logout.")
    sys.exit(1)

payload = {
    "refreshToken": refresh_token
}

print("--- Logging Out ---")

utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/logout",
    method="POST",
    body=payload,
    output_file=OUTPUT_FILE
)