import sys
import os
import time

# Add current directory to path to import utils
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Define Output Filename
OUTPUT_FILE = f"{os.path.splitext(os.path.basename(__file__))[0]}.json"

# Generate a unique email to avoid "Email already taken" errors during testing
unique_id = int(time.time())
email = f"testuser_{unique_id}@example.com"
password = "password123"
name = f"Test User {unique_id}"

# Payload
payload = {
    "name": name,
    "email": email,
    "password": password
}

print(f"--- Registering User: {email} ---")

# Send Request
response = utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/register",
    method="POST",
    body=payload,
    output_file=OUTPUT_FILE
)

if response.status_code == 201:
    print("\n[SUCCESS] User registered.")
    utils.save_config("test_email", email)
    utils.save_config("test_password", password)
else:
    print(f"\n[FAIL] Registration failed with status {response.status_code}")