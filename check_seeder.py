import re
from collections import Counter

content = open('database/seeders/LocationSeeder.php', 'r', encoding='utf-8').read()

# Find all 'id' => X entries
ids = re.findall(r"'id' => (\d+)", content)
counts = Counter(ids)

duplicates = {k: v for k, v in counts.items() if v > 1}
if duplicates:
    print(f"Found duplicate IDs: {duplicates}")
else:
    print("No duplicate IDs found.")

# Find all occurrences of the arrays themselves
state_matches = re.findall(r"\$states = \[", content)
municipality_matches = re.findall(r"\$municipalities = \[", content)
parish_matches = re.findall(r"\$parishes = \[", content)

print(f"States definitions: {len(state_matches)}")
print(f"Municipalities definitions: {len(municipality_matches)}")
print(f"Parish definitions: {len(parish_matches)}")
