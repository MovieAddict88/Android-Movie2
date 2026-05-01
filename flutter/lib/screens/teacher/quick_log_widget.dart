import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class QuickLogWidget extends StatelessWidget {
  const QuickLogWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Shortcuts(
      shortcuts: <LogicalKeySet, Intent>{
        LogicalKeySet(LogicalKeyboardKey.control, LogicalKeyboardKey.keyP): const _LogPositiveIntent(),
        LogicalKeySet(LogicalKeyboardKey.control, LogicalKeyboardKey.keyN): const _LogNegativeIntent(),
      },
      child: Actions(
        actions: <Type, Action<Intent>>{
          _LogPositiveIntent: CallbackAction<_LogPositiveIntent>(onInvoke: (intent) => _log('Positive')),
          _LogNegativeIntent: CallbackAction<_LogNegativeIntent>(onInvoke: (intent) => _log('Negative')),
        },
        child: Focus(
          autofocus: true,
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(8.0),
              child: Column(
                children: [
                  const Text('Quick Log (Ctrl+P for Positive, Ctrl+N for Negative)'),
                  Row(
                    children: [
                      ElevatedButton(onPressed: () => _log('Positive'), child: const Text('Positive')),
                      ElevatedButton(onPressed: () => _log('Negative'), child: const Text('Negative')),
                    ],
                  )
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _log(String type) {
    // Logging logic
  }
}

class _LogPositiveIntent extends Intent { const _LogPositiveIntent(); }
class _LogNegativeIntent extends Intent { const _LogNegativeIntent(); }
