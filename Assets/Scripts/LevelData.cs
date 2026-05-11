using System.Collections;
using System.Collections.Generic;
using UnityEngine;

[System.Serializable]
public class LevelData
{
    public string centerLetters; // The letters in the circle (e.g., "ACT")
    public List<WordPosition> words; // The words to be found and their positions
}

[System.Serializable]
public class WordPosition
{
    public string word;
    public int startX;
    public int startY;
    public bool isVertical;
}
