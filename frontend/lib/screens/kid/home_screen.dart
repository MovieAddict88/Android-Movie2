import 'package:flutter/material.dart';
import 'package:lottie/lottie.dart';

class KidHomeScreen extends StatefulWidget {
  @override
  _KidHomeScreenState createState() => _KidHomeScreenState();
}

class _KidHomeScreenState extends State<KidHomeScreen> {
  final List<Map<String, dynamic>> _chores = [
    {'id': 1, 'name': 'Brush Teeth', 'gems': 5, 'completed': false},
    {'id': 2, 'name': 'Make Bed', 'gems': 10, 'completed': false},
    {'id': 3, 'name': 'Feed the Dog', 'gems': 15, 'completed': false},
  ];

  int _totalGems = 120;

  void _completeChore(int index) {
    setState(() {
      _chores[index]['completed'] = true;
      _totalGems += (_chores[index]['gems'] as int);
    });

    // Show completion animation
    _showCompletionDialog(_chores[index]['name']);
  }

  void _showCompletionDialog(String choreName) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.yellow[100],
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('AMAZING!', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.orange)),
            SizedBox(height: 10),
            Text('You finished: $choreName', textAlign: TextAlign.center),
            SizedBox(height: 20),
            // Placeholder for Lottie animation
            Icon(Icons.star, size: 100, color: Colors.orange),
            SizedBox(height: 20),
            ElevatedButton(
              onPressed: () => Navigator.pop(context),
              child: Text('Next Quest!'),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
            )
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('My Quests'),
        backgroundColor: Colors.purple,
        actions: [
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Icon(Icons.diamond, color: Colors.cyanAccent),
                SizedBox(width: 5),
                Text('$_totalGems', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
              ],
            ),
          )
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.purple[50]!, Colors.blue[50]!],
          ),
        ),
        child: ListView.builder(
          padding: EdgeInsets.all(16),
          itemCount: _chores.length,
          itemBuilder: (context, index) {
            final chore = _chores[index];
            return Card(
              elevation: 4,
              margin: EdgeInsets.only(bottom: 16),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: chore['completed'] ? Colors.green : Colors.orange,
                  child: Icon(chore['completed'] ? Icons.check : Icons.directions_run, color: Colors.white),
                ),
                title: Text(chore['name'], style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
                subtitle: Text('Reward: ${chore['gems']} Gems'),
                trailing: chore['completed']
                  ? Icon(Icons.check_circle, color: Colors.green)
                  : ElevatedButton(
                      onPressed: () => _completeChore(index),
                      child: Text('Done!'),
                      style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                    ),
              ),
            );
          },
        ),
      ),
      bottomNavigationBar: BottomNavigationBar(
        items: [
          BottomNavigationBarItem(icon: Icon(Icons.map), label: 'Quests'),
          BottomNavigationBarItem(icon: Icon(Icons.shopping_cart), label: 'Shop'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Avatar'),
        ],
      ),
    );
  }
}
