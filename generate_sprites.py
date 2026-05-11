from PIL import Image, ImageDraw

def create_rounded_rectangle(draw, shape, radius, fill):
    upper_left = shape[0]
    lower_right = shape[1]
    draw.pieslice([upper_left, (upper_left[0] + radius * 2, upper_left[1] + radius * 2)], 180, 270, fill=fill)
    draw.pieslice([(lower_right[0] - radius * 2, upper_left[1]), (lower_right[0], upper_left[1] + radius * 2)], 270, 360, fill=fill)
    draw.pieslice([(lower_right[0] - radius * 2, lower_right[1] - radius * 2), (lower_right[0], lower_right[1])], 0, 90, fill=fill)
    draw.pieslice([(upper_left[0], lower_right[1] - radius * 2), (upper_left[0] + radius * 2, lower_right[1])], 90, 180, fill=fill)
    draw.rectangle([(upper_left[0] + radius, upper_left[1]), (lower_right[0] - radius, lower_right[1])], fill=fill)
    draw.rectangle([(upper_left[0], upper_left[1] + radius), (lower_right[0], lower_right[1] - radius)], fill=fill)

# 1. Background
bg = Image.new('RGBA', (1080, 1920), (240, 248, 255, 255)) # AliceBlue
bg.save('Assets/Sprites/background.png')

# 2. Letter Tile
tile = Image.new('RGBA', (256, 256), (0, 0, 0, 0))
draw = ImageDraw.Draw(tile)
create_rounded_rectangle(draw, [(10, 10), (246, 246)], 40, (255, 255, 255, 255))
# Add a subtle shadow/border
draw.arc([(10, 10), (246, 246)], 0, 360, fill=(200, 200, 200, 255), width=4)
tile.save('Assets/Sprites/tile.png')

# 3. Circle BG
circle = Image.new('RGBA', (512, 512), (0, 0, 0, 0))
draw = ImageDraw.Draw(circle)
draw.ellipse([20, 20, 492, 492], fill=(255, 255, 255, 100), outline=(255, 255, 255, 150), width=8)
circle.save('Assets/Sprites/circle_bg.png')

# 4. Button
btn = Image.new('RGBA', (400, 120), (0, 0, 0, 0))
draw = ImageDraw.Draw(btn)
create_rounded_rectangle(draw, [(5, 5), (395, 115)], 30, (100, 200, 100, 255))
btn.save('Assets/Sprites/button.png')

# 5. Connection Line
line = Image.new('RGBA', (64, 64), (255, 255, 255, 255))
line.save('Assets/Sprites/connection_line.png')

print("Sprites generated successfully.")
