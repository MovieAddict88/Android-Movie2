import 'package:flutter/material.dart';

class BehaviorFeedScreen extends StatefulWidget {
  const BehaviorFeedScreen({super.key});

  @override
  State<BehaviorFeedScreen> createState() => _BehaviorFeedScreenState();
}

class _BehaviorFeedScreenState extends State<BehaviorFeedScreen> {
  final List<Map<String, dynamic>> _behaviors = [];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Behavior Feed')),
      body: ListView.builder(
        itemCount: _behaviors.length,
        itemBuilder: (context, index) {
          final behavior = _behaviors[index];
          final bool isTranslated = behavior['is_translated'] ?? false;

          return Card(
            child: Column(
              children: [
                ListTile(
                  title: Text(behavior['content']),
                  subtitle: isTranslated
                    ? const Text(
                        'AI-generated translation. Contact school for official version.',
                        style: TextStyle(fontSize: 10, fontStyle: FontStyle.italic),
                      )
                    : null,
                  trailing: isTranslated
                    ? const Chip(label: Text('Translated', style: TextStyle(fontSize: 10)))
                    : null,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
