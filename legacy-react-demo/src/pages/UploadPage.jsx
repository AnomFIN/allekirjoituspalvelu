import { useCallback, useEffect, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { useNavigate } from 'react-router-dom';

export default function UploadPage() {
  const navigate = useNavigate();
  const [file, setFile] = useState(null);
  const [preview, setPreview] = useState(null);
  const [error, setError] = useState('');

  const getDropErrorMessage = (rejected) => {
    const messages = rejected.flatMap(({ errors }) =>
      errors.map(({ code }) => {
        switch (code) {
          case 'file-invalid-type':
            return 'Vain PDF-tiedostot ovat sallittuja. Tarkista tiedostomuoto.';
          case 'file-too-large':
            return 'Tiedosto on liian suuri. Suurin sallittu koko on 20 Mt.';
          case 'too-many-files':
            return 'Voit ladata vain yhden tiedoston kerrallaan.';
          default:
            return 'Tiedoston lataus epäonnistui. Tarkista tiedosto ja yritä uudelleen.';
        }
      })
    );

    return [...new Set(messages)].join(' ');
  };

  const onDrop = useCallback((accepted, rejected) => {
    setError('');
    if (rejected.length > 0) {
      setError(getDropErrorMessage(rejected));
      return;
    }
    if (accepted.length > 0) {
      const f = accepted[0];
      setFile(f);
      setPreview(URL.createObjectURL(f));
    }
  }, []);

  // Revoke the object URL when preview changes or on unmount to avoid memory leaks.
  useEffect(() => {
    return () => {
      if (preview) URL.revokeObjectURL(preview);
    };
  }, [preview]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: { 'application/pdf': ['.pdf'] },
    multiple: false,
    maxSize: 20 * 1024 * 1024, // 20 MB
  });

  const handleContinue = () => {
    if (file) {
      // Store file name in sessionStorage for demo
      sessionStorage.setItem('uploadedDoc', file.name);
      navigate('/upload/signers', { state: { fileName: file.name } });
    }
  };

  const handleReset = () => {
    setFile(null);
    setPreview(null);
    setError('');
  };

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      {/* Page header */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">Lataa dokumentti</h1>
        <p className="text-slate-500 mt-1 text-sm">
          Lataa PDF-tiedosto ja lähetä se allekirjoitettavaksi.
        </p>
      </div>

      {/* Steps */}
      <div className="flex items-center gap-0">
        {['Lataa tiedosto', 'Lisää allekirjoittajat', 'Lähetä pyyntö'].map((step, i) => (
          <div key={i} className="flex items-center flex-1 last:flex-none">
            <div className="flex flex-col items-center">
              <div className={`w-7 h-7 rounded-full flex items-center justify-center text-sm font-semibold border-2 ${
                i === 0
                  ? 'bg-blue-900 border-blue-900 text-white'
                  : 'bg-white border-slate-300 text-slate-400'
              }`}>
                {i + 1}
              </div>
              <span className={`text-xs mt-1 font-medium whitespace-nowrap ${
                i === 0 ? 'text-blue-900' : 'text-slate-400'
              }`}>
                {step}
              </span>
            </div>
            {i < 2 && (
              <div className="flex-1 h-0.5 bg-slate-200 mx-2 mb-4" />
            )}
          </div>
        ))}
      </div>

      {/* Upload area */}
      {!file ? (
        <div>
          <div
            {...getRootProps()}
            className={`border-2 border-dashed rounded-2xl p-10 md:p-16 text-center cursor-pointer transition-all ${
              isDragActive
                ? 'border-blue-500 bg-blue-50'
                : 'border-slate-300 bg-white hover:border-blue-400 hover:bg-blue-50/30'
            }`}
          >
            <input {...getInputProps()} />
            <div className={`w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 transition-colors ${
              isDragActive ? 'bg-blue-100' : 'bg-slate-100'
            }`}>
              <svg className={`w-8 h-8 ${isDragActive ? 'text-blue-600' : 'text-slate-400'}`}
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>

            {isDragActive ? (
              <p className="text-blue-700 font-semibold text-lg">Pudota tiedosto tähän</p>
            ) : (
              <>
                <p className="text-slate-700 font-semibold text-lg">
                  Vedä ja pudota PDF-tiedosto tähän
                </p>
                <p className="text-slate-400 text-sm mt-1">tai</p>
                <span className="inline-block mt-3 bg-blue-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors">
                  Valitse tiedosto
                </span>
                <p className="text-slate-400 text-xs mt-4">
                  Vain PDF-tiedostot • Enintään 20 Mt
                </p>
              </>
            )}
          </div>

          {error && (
            <div className="mt-3 flex items-center gap-2 text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-4 py-2.5">
              <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {error}
            </div>
          )}
        </div>
      ) : (
        <div className="space-y-4">
          {/* File info */}
          <div className="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg className="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <p className="font-medium text-slate-900 text-sm">{file.name}</p>
                <p className="text-xs text-slate-400">
                  {(file.size / 1024).toFixed(1)} kt • PDF
                </p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <span className="flex items-center gap-1.5 text-xs text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                </svg>
                Valmis
              </span>
              <button
                onClick={handleReset}
                className="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          {/* PDF Preview */}
          <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div className="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center gap-2">
              <svg className="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <span className="text-xs font-medium text-slate-600">Esikatselu</span>
            </div>
            <iframe
              src={preview}
              title="PDF esikatselu"
              className="w-full h-96"
              style={{ border: 'none' }}
            />
          </div>

          {/* Continue button */}
          <button
            onClick={handleContinue}
            className="w-full bg-blue-900 text-white py-3.5 rounded-xl font-semibold hover:bg-blue-800 transition-colors shadow-sm flex items-center justify-center gap-2"
          >
            Jatka – Lisää allekirjoittajat
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      )}
    </div>
  );
}
