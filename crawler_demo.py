import requests
import re
import json
import sys

def extract_hydration_data(url):
    """
    Fetches a NetShort page and extracts JSON data from Next.js hydration scripts.
    """
    print(f"Fetching: {url}")
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    }

    try:
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()
    except Exception as e:
        print(f"Error fetching URL: {e}")
        return None

    # Regex to find self.__next_f.push calls
    # Format: self.__next_f.push([1,"DATA"])
    pattern = re.compile(r'self\.__next_f\.push\(\[[0-9]+,"(.*?)"\]\)')
    matches = pattern.findall(response.text)

    found_data = []
    for match in matches:
        # Unescape the string to make it valid JSON
        # Next.js escapes quotes and other chars
        decoded = match.replace('\\"', '"').replace('\\\\', '\\')

        # Look for videoList or VideoObject patterns
        if "videoList" in decoded or "VideoObject" in decoded or "shortPlayId" in decoded:
            try:
                # Attempt to find actual JSON blocks within the component props
                # This is a simplification; a full parser would be more robust
                json_match = re.search(r'\{.*\}', decoded)
                if json_match:
                    item = json.loads(json_match.group())
                    found_data.append(item)
            except:
                continue

    return found_data

if __name__ == "__main__":
    test_url = "https://netshort.com/episode/the-wolfless-carpenter-rules-the-world-2050068409881722882"
    if len(sys.argv) > 1:
        test_url = sys.argv[1]

    data = extract_hydration_data(test_url)
    if data:
        print(f"Successfully extracted {len(data)} metadata blocks.")
        # Print a snippet of the first found block
        print(json.dumps(data[0], indent=2)[:500] + "...")
    else:
        print("No metadata found in hydration scripts.")
