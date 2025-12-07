import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

# Admin
email = "admin@example.com"
password = "password123"

payload = {
    "email": email,
    "password": password
}

print(f"--- Logging in as: {email} ---")

response = utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/login",
    method="POST",
    body=payload,
    output_file=OUTPUT_FILE
)

data = response.json()

if response.status_code == 200 and data:
    # Extract Tokens and User ID
    access_token = data.get("tokens", {}).get("access", {}).get("token")
    refresh_token = data.get("tokens", {}).get("refresh", {}).get("token")
    user_id = data.get("user", {}).get("id")

    if access_token:
        utils.save_config("access_token", access_token)
        print("-> Saved 'access_token' to secrets.json")
    
    if refresh_token:
        utils.save_config("refresh_token", refresh_token)
        print("-> Saved 'refresh_token' to secrets.json")

    if user_id:
        utils.save_config("user_id", user_id)
        print(f"-> Saved 'user_id' ({user_id}) to secrets.json")
else:
    print("\n[FAIL] Login failed. Could not save tokens.")