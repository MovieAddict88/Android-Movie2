using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.EventSystems;

public class InputController : MonoBehaviour
{
    public RectTransform circleTransform;
    public GameObject letterPrefab;
    public LineRenderer lineRenderer;
    public Text currentWordText;

    private List<GameObject> circleButtons = new List<GameObject>();
    private List<Vector3> selectedPositions = new List<Vector3>();
    private string currentWord = "";
    private bool isDragging = false;
    private HashSet<char> selectedLetters = new HashSet<char>();

    public void SetupCircle(string letters)
    {
        foreach (var btn in circleButtons) Destroy(btn);
        circleButtons.Clear();

        float radius = circleTransform.rect.width / 2.5f;
        for (int i = 0; i < letters.Length; i++)
        {
            char letter = letters[i];
            float angle = i * Mathf.PI * 2 / letters.Length;
            Vector2 pos = new Vector2(Mathf.Cos(angle) * radius, Mathf.Sin(angle) * radius);

            GameObject btn = Instantiate(letterPrefab, circleTransform);
            btn.GetComponent<RectTransform>().anchoredPosition = pos;
            btn.GetComponentInChildren<Text>().text = letter.ToString();

            // Add EventTrigger programmatically
            EventTrigger trigger = btn.gameObject.AddComponent<EventTrigger>();

            // Pointer Down
            EventTrigger.Entry entryDown = new EventTrigger.Entry();
            entryDown.eventID = EventTriggerType.PointerDown;
            entryDown.callback.AddListener((data) => { OnStartDragging(letter, btn.transform.position); });
            trigger.triggers.Add(entryDown);

            // Pointer Enter
            EventTrigger.Entry entryEnter = new EventTrigger.Entry();
            entryEnter.eventID = EventTriggerType.PointerEnter;
            entryEnter.callback.AddListener((data) => { OnLetterPointerEnter(letter, btn.transform.position); });
            trigger.triggers.Add(entryEnter);

            circleButtons.Add(btn);
        }
    }

    void Update()
    {
        if (isDragging)
        {
            if (Input.GetMouseButtonUp(0))
            {
                OnEndDragging();
            }
            else
            {
                UpdateLine();
            }
        }
    }

    void OnStartDragging(char letter, Vector3 position)
    {
        isDragging = true;
        currentWord = letter.ToString();
        selectedLetters.Clear();
        selectedLetters.Add(letter);
        selectedPositions.Clear();
        selectedPositions.Add(position);
        currentWordText.text = currentWord;

        lineRenderer.positionCount = 1;
        lineRenderer.SetPosition(0, position);
    }

    void OnLetterPointerEnter(char letter, Vector3 position)
    {
        if (isDragging && !selectedLetters.Contains(letter))
        {
            selectedLetters.Add(letter);
            currentWord += letter;
            selectedPositions.Add(position);
            currentWordText.text = currentWord;

            lineRenderer.positionCount = selectedPositions.Count;
            lineRenderer.SetPosition(selectedPositions.Count - 1, position);
        }
    }

    void OnEndDragging()
    {
        isDragging = false;
        GameManager.Instance.OnWordSubmitted(currentWord);
        currentWordText.text = "";
        lineRenderer.positionCount = 0;
        selectedLetters.Clear();
        selectedPositions.Clear();
    }

    void UpdateLine()
    {
        if (selectedPositions.Count > 0)
        {
            // Set the last point of the line to the mouse position for smooth dragging
            lineRenderer.positionCount = selectedPositions.Count + 1;
            Vector3 mousePos = Input.mousePosition;
            mousePos.z = 10; // Distance from camera
            lineRenderer.SetPosition(selectedPositions.Count, Camera.main.ScreenToWorldPoint(mousePos));
        }
    }
}
