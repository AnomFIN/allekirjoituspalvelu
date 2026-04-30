import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import UploadPage from './pages/UploadPage';
import AddSignersPage from './pages/AddSignersPage';
import SentPage from './pages/SentPage';
import DocumentsPage from './pages/DocumentsPage';
import DocumentDetailPage from './pages/DocumentDetailPage';
import SignPage from './pages/SignPage';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Public signing route – no layout wrapper */}
        <Route path="/sign/:token" element={<SignPage />} />
        {/* Demo sign route without token */}
        <Route path="/sign" element={<SignPage />} />

        {/* App routes with main layout */}
        <Route
          path="/*"
          element={
            <Layout>
              <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/upload" element={<UploadPage />} />
                <Route path="/upload/signers" element={<AddSignersPage />} />
                <Route path="/upload/sent" element={<SentPage />} />
                <Route path="/documents" element={<DocumentsPage />} />
                <Route path="/documents/:id" element={<DocumentDetailPage />} />
              </Routes>
            </Layout>
          }
        />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
