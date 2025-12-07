import sys
import os
import time

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

access_token = utils.load_config("access_token")

if not access_token:
    print("[ERROR] No access_token found. Run A2.auth_login.py first.")
    sys.exit(1)

# Unique data for new user
unique_id = int(time.time())
payload = {
    "name": f"Admin Created User {unique_id}",
    "email": f"created_{unique_id}@example.com",
    "password": "password123",
    "role": "user" 
}

headers = {
    "Authorization": f"Bearer {access_token}"
}

print("--- Creating User (Requires Admin Role) ---")

utils.send_and_print(
    url=f"{utils.BASE_URL}/users",
    method="POST",
    headers=headers,
    body=payload,
    output_file=OUTPUT_FILE
)