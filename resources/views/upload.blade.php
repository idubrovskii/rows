<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rows Upload</title>
</head>
<body>
  <h2>Upload a File</h2>
  <form id="uploadForm">
    @csrf
    <input type="file" id="rowsfile" />
    <p id="rowsfile"></p>
    <button type="submit">Upload</button>
  </form>

  <script>
    const rowsfile = document.getElementById('rowsfile');
    const token = document.getElementsByName('_token')[0].value;
    const rowsfileDisplay = document.getElementById('rowsfile');
    const uploadForm = document.getElementById('uploadForm');

    rowsfile.addEventListener('change', () => {
      if (rowsfile.files.length > 0) {
        rowsfileDisplay.textContent = `Selected file: ${rowsfile.files[0].name}`;
      } else {
        rowsfileDisplay.textContent = '';
      }
    });

    uploadForm.addEventListener('submit', (e) => {
      e.preventDefault();

      if (rowsfile.files.length === 0) {
        alert('Please select a file first.');
        return;
      }

      const formData = new FormData();
      formData.append('_token', token);
      formData.append('rowsfile', rowsfile.files[0]);

      fetch("{{route('file.upload')}}", {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => alert('File uploaded successfully'))
      .catch(error => alert('Upload failed: ' + error));
    });
  </script>
</body>
</html>
