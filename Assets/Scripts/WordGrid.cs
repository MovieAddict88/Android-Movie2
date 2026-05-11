using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class WordGrid : MonoBehaviour
{
    public GameObject tilePrefab;
    public float tileSize = 110f; // Slightly larger for better spacing
    private Dictionary<Vector2Int, LetterTile> gridTiles = new Dictionary<Vector2Int, LetterTile>();
    private Dictionary<string, List<LetterTile>> wordToTiles = new Dictionary<string, List<LetterTile>>();

    public void SetupGrid(LevelData data)
    {
        foreach (Transform child in transform)
        {
            Destroy(child.gameObject);
        }
        gridTiles.Clear();
        wordToTiles.Clear();

        // Calculate center to offset the grid
        int minX = int.MaxValue, maxX = int.MinValue, minY = int.MaxValue, maxY = int.MinValue;
        foreach (var wp in data.words)
        {
            for (int i = 0; i < wp.word.Length; i++)
            {
                int x = wp.startX + (wp.isVertical ? 0 : i);
                int y = wp.startY - (wp.isVertical ? i : 0);
                minX = Mathf.Min(minX, x);
                maxX = Mathf.Max(maxX, x);
                minY = Mathf.Min(minY, y);
                maxY = Mathf.Max(maxY, y);
            }
        }

        float offsetX = -(minX + maxX) * tileSize / 2f;
        float offsetY = -(minY + maxY) * tileSize / 2f;

        foreach (var wp in data.words)
        {
            List<LetterTile> tilesInWord = new List<LetterTile>();
            for (int i = 0; i < wp.word.Length; i++)
            {
                int x = wp.startX + (wp.isVertical ? 0 : i);
                int y = wp.startY - (wp.isVertical ? i : 0);
                Vector2Int pos = new Vector2Int(x, y);

                LetterTile tile;
                if (!gridTiles.ContainsKey(pos))
                {
                    GameObject tileObj = Instantiate(tilePrefab, transform);
                    RectTransform rect = tileObj.GetComponent<RectTransform>();
                    rect.anchoredPosition = new Vector2(x * tileSize + offsetX, y * tileSize + offsetY);

                    tile = tileObj.GetComponent<LetterTile>();
                    tile.SetLetter(wp.word[i]);
                    gridTiles[pos] = tile;
                }
                else
                {
                    tile = gridTiles[pos];
                }
                tilesInWord.Add(tile);
            }
            wordToTiles[wp.word] = tilesInWord;
        }
    }

    public void RevealWord(string word)
    {
        if (wordToTiles.ContainsKey(word))
        {
            foreach (var tile in wordToTiles[word])
            {
                tile.Reveal();
            }
        }
    }
}
