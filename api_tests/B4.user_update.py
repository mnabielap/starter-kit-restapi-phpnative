import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

access_token = utils.load_config("access_token")
user_id = utils.load_config("user_id")

if not access_token or not user_id:
    print("[ERROR] Missing credentials.")
    sys.exit(1)

# Update the name
payload = {
    "name": "Updated Name via Python Script"
}

headers = {
    "Authorization": f"Bearer {access_token}"
}

print(f"--- Updating User ID: {user_id} ---")

utils.send_and_print(
    url=f"{utils.BASE_URL}/users/{user_id}",
    method="PATCH",
    headers=headers,
    body=payload,
    output_file=OUTPUT_FILE
)