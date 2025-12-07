import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

# Load Refresh Token
refresh_token = utils.load_config("refresh_token")

if not refresh_token:
    print("[ERROR] No refresh_token found in secrets.json. Run A2.auth_login.py first.")
    sys.exit(1)

payload = {
    "refreshToken": refresh_token
}

print("--- Refreshing Tokens ---")

response = utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/refresh-tokens",
    method="POST",
    body=payload,
    output_file=OUTPUT_FILE
)

data = response.json()

if response.status_code == 200 and data:
    # Update secrets with new tokens
    new_access = data.get("access", {}).get("token")
    new_refresh = data.get("refresh", {}).get("token")

    if new_access:
        utils.save_config("access_token", new_access)
        print("-> Updated 'access_token'")
    if new_refresh:
        utils.save_config("refresh_token", new_refresh)
        print("-> Updated 'refresh_token'")