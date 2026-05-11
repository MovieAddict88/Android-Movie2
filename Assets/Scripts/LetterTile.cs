using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;

public class LetterTile : MonoBehaviour
{
    public Text letterText;
    public Image background;
    private string letter;
    private bool isRevealed = false;

    public void SetLetter(char c)
    {
        letter = c.ToString();
        letterText.text = ""; // Hide initially
        isRevealed = false;
    }

    public void Reveal()
    {
        if (!isRevealed)
        {
            isRevealed = true;
            letterText.text = letter;
            // Add animation or color change here
            background.color = Color.white;
        }
    }
}
