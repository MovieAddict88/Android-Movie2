using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
using System.IO;

public class GameManager : MonoBehaviour
{
    public static GameManager Instance;

    [Header("UI References")]
    public Text levelText;
    public WordGrid wordGrid;
    public InputController inputController;
    public GameObject winPanel;

    private int currentLevel = 1;
    private LevelData currentLevelData;
    private HashSet<string> foundWords = new HashSet<string>();

    void Awake()
    {
        Instance = this;
    }

    void Start()
    {
        LoadLevel(currentLevel);
    }

    public void LoadLevel(int levelNumber)
    {
        currentLevel = levelNumber;
        levelText.text = "Level " + currentLevel;
        winPanel.SetActive(false);
        foundWords.Clear();

        string jsonPath = "Levels/Level" + levelNumber;
        TextAsset levelJson = Resources.Load<TextAsset>(jsonPath);

        if (levelJson != null)
        {
            currentLevelData = JsonUtility.FromJson<LevelData>(levelJson.text);
            wordGrid.SetupGrid(currentLevelData);
            inputController.SetupCircle(currentLevelData.centerLetters);
        }
        else
        {
            Debug.LogError("Level not found: " + jsonPath);
        }
    }

    public void OnWordSubmitted(string word)
    {
        bool isCorrect = false;
        foreach (var wp in currentLevelData.words)
        {
            if (wp.word == word && !foundWords.Contains(word))
            {
                isCorrect = true;
                foundWords.Add(word);
                wordGrid.RevealWord(word);
                break;
            }
        }

        if (foundWords.Count == currentLevelData.words.Count)
        {
            Invoke("ShowWinPanel", 1.0f);
        }
    }

    void ShowWinPanel()
    {
        winPanel.SetActive(true);
    }

    public void NextLevel()
    {
        LoadLevel(currentLevel + 1);
    }
}
