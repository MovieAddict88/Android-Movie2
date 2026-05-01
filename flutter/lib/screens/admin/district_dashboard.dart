import 'package:flutter/material.dart';

class DistrictDashboard extends StatelessWidget {
  const DistrictDashboard({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('District Analytics')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: GridView.count(
          crossAxisCount: 2,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          children: [
            _statCard('Total Students', '12,450', Icons.people),
            _statCard('Avg. Attendance', '94%', Icons.calendar_today),
            _statCard('Positive Behaviors', '45,200', Icons.thumb_up),
            _statCard('Interventions Req.', '28', Icons.warning, color: Colors.orange),
          ],
        ),
      ),
    );
  }

  Widget _statCard(String title, String value, IconData icon, {Color color = Colors.blue}) {
    return Card(
      elevation: 4,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 40, color: color),
            const SizedBox(height: 10),
            Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
            const SizedBox(height: 5),
            Text(title, textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }
}
