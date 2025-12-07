import sys
import os

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

access_token = utils.load_config("access_token")

if not access_token:
    print("[ERROR] No access_token found.")
    sys.exit(1)

headers = {
    "Authorization": f"Bearer {access_token}"
}

# Query params example
query_params = "?limit=10&page=1"

print("--- Getting All Users (Requires Admin Role) ---")

utils.send_and_print(
    url=f"{utils.BASE_URL}/users{query_params}",
    method="GET",
    headers=headers,
    output_file=OUTPUT_FILE
)