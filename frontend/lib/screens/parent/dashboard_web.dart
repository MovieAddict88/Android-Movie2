import 'package:flutter/material.dart';

class ParentDashboardWeb extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('ChoreQuest Parent Portal'),
        backgroundColor: Colors.indigo,
        actions: [
          IconButton(icon: Icon(Icons.settings), onPressed: () {}),
          IconButton(icon: Icon(Icons.logout), onPressed: () {}),
        ],
      ),
      body: Row(
        children: [
          // Sidebar
          Container(
            width: 250,
            color: Colors.grey[100],
            child: ListView(
              children: [
                ListTile(leading: Icon(Icons.dashboard), title: Text('Dashboard'), selected: true),
                ListTile(leading: Icon(Icons.list), title: Text('Manage Chores')),
                ListTile(leading: Icon(Icons.child_care), title: Text('Children')),
                ListTile(leading: Icon(Icons.card_giftcard), title: Text('Approvals')),
                ListTile(leading: Icon(Icons.analytics), title: Text('Reports')),
                ListTile(leading: Icon(Icons.security), title: Text('AI Safety Logs')),
              ],
            ),
          ),
          // Main Content
          Expanded(
            child: SingleChildScrollView(
              padding: EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Family Overview', style: Theme.of(context).textTheme.headlineMedium),
                  SizedBox(height: 24),
                  Row(
                    children: [
                      _buildSummaryCard('Pending Approvals', '5', Colors.orange),
                      SizedBox(width: 16),
                      _buildSummaryCard('Chores Done Today', '12', Colors.green),
                      SizedBox(width: 16),
                      _buildSummaryCard('Gems Awarded', '150', Colors.blue),
                    ],
                  ),
                  SizedBox(height: 32),
                  Text('Recent Activity', style: Theme.of(context).textTheme.headlineSmall),
                  SizedBox(height: 16),
                  _buildActivityTable(),
                ],
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {},
        label: Text('New Chore'),
        icon: Icon(Icons.add),
        backgroundColor: Colors.indigo,
      ),
    );
  }

  Widget _buildSummaryCard(String title, String value, Color color) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: Column(
            children: [
              Text(title, style: TextStyle(color: Colors.grey[600])),
              SizedBox(height: 8),
              Text(value, style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: color)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildActivityTable() {
    return Card(
      child: DataTable(
        columns: [
          DataColumn(label: Text('Child')),
          DataColumn(label: Text('Action')),
          DataColumn(label: Text('Status')),
          DataColumn(label: Text('Time')),
        ],
        rows: [
          DataRow(cells: [
            DataCell(Text('Emma')),
            DataCell(Text('Completed "Clean Room"')),
            DataCell(Chip(label: Text('Pending'), backgroundColor: Colors.orange[100])),
            DataCell(Text('10 mins ago')),
          ]),
          DataRow(cells: [
            DataCell(Text('Alex')),
            DataCell(Text('Redeemed "AI Story"')),
            DataCell(Chip(label: Text('Approved'), backgroundColor: Colors.green[100])),
            DataCell(Text('1 hour ago')),
          ]),
        ],
      ),
    );
  }
}
