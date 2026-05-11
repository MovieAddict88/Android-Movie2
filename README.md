# Word Puzzle Game for Kids (Unity/Android)

A professional, kid-friendly word puzzle game featuring crossword-style grids and a letter connection circle.

## Features
- **Fully Interactive**: Connect letters in a circle to form words with real-time visual feedback.
- **Dynamic Crossword Grids**: Words are revealed in a centered grid as they are found. Handles overlapping letters automatically.
- **Professional Assets**: Custom-generated sprites with kid-friendly colors and rounded corners.
- **Easy Level Design**: Levels are loaded from JSON files.

## Project Structure
- `Assets/Scripts`: Core game logic.
  - `GameManager.cs`: Main controller for level flow and win conditions.
  - `InputController.cs`: Handles touch/mouse interaction and line drawing.
  - `WordGrid.cs`: Manages the crossword grid layout.
- `Assets/Sprites`: UI assets (background, tile, button, circle_bg).
- `Assets/Resources/Levels`: JSON level definitions.

## How to Setup in Unity

### 1. Scene Structure
- **Main Camera**: Set background color to match `background.png`.
- **Canvas (Overlay)**:
  - **Background**: Add an Image with `background.png`.
  - **Grid Area**: Create an empty UI object with `WordGrid` script.
  - **Input Circle**:
    - Create a UI Image with `circle_bg.png`.
    - Add `InputController` script.
    - Attach a `LineRenderer` component to this object (Set "Use World Space" to false).
  - **UI Text**: Add a Text object for "Current Word" and "Level Number".
  - **Win Panel**: Create a panel that is hidden by default.

### 2. Prefabs
- **LetterTile**: A UI Image (`tile.png`) with a child Text component. Attach `LetterTile.cs`.
- **LetterButton**: A UI Image (`button.png`) with a child Text component.

### 3. Links
- Drag the `LetterTile` prefab into the `WordGrid`'s `Tile Prefab` slot.
- Drag the `LetterButton` prefab into the `InputController`'s `Letter Prefab` slot.
- Link all UI references in the `GameManager` script.

## Customizing Levels
Add JSON files to `Assets/Resources/Levels/`:
```json
{
    "centerLetters": "ACT",
    "words": [
        { "word": "CAT", "startX": 0, "startY": 0, "isVertical": false },
        { "word": "ACT", "startX": 0, "startY": 0, "isVertical": true }
    ]
}
```
